<?php


abstract class View
{

	private static $cached_views;

	protected $Model;

	protected $__screen_names = array();


/******************************************************************************/
/********************************* S T A T I C ********************************/
/******************************************************************************/

	final public static function get($viewname)
	{
		if (!$viewname)
		{
			$msg = "Trying to initialize a view without a view name";
			throw new Exception($msg);
		}

		$ucview = ucfirst($viewname);

		if (!preg_match('/\w+/', $viewname))
		{
			$msg = "Illegal character in View name: {$ucview}";
			throw new Exception($msg);
		}

		if (empty(self::$cached_views[$ucview]))
		{
			$path = APP_VIEWS . "/{$ucview}.php";

			if (is_file($path))
			{
				require_once $path;
				$class = "View_{$ucview}";

				if (!class_exists($class))
				{
					$msg = "View class for {$ucview} not found";
					throw new Exception($msg);
				}

				$View = new $class($viewname);
			}
			else
			{
				require_once dirname(__FILE__) . '/AbstractView.php';
				$View = new AbstractView($viewname);
			}

			self::$cached_views[$viewname] = $View;
		}

		return self::$cached_views[$viewname];
	}


/******************************************************************************/
/******************************* I N S T A N C E ******************************/
/******************************************************************************/

	/**
	 * Block descendants from being directly initialized, using final keyword.
	 */
	final public function __construct($view)
	{
		$this->Model = Model::get($view);

		// Register common attributes for the view
		$this->assign('name',   $this->name);
		$this->assign('plural', $this->plural);
		$this->assign('gender', $this->gender);
	}

	/**
	 * protected string name()
	 *      Called when no @name was defined (or ::name in sub-class), returns
	 * a default name for the view, based on the Model's table name.
	 *
	 * @return string
	 */
	protected function name()
	{
		return $this->__name ? $this->__name : ucfirst($this->Model->table);
	}

	/**
	 * protected string plural()
	 *      Called when no @plural's defined (or ::plural in sub-class), returns
	 * a default name in plural for the view, based on the name's singular form.
	 *
	 * @return string
	 */
	protected function plural()
	{
		$s = preg_match('/s$/', $this->name) ? 'es' : 's';
		return $this->__plural ? $this->__plural : ucfirst($this->name) . $s;
	}

	/**
	 * protected string gender()
	 *      Called when no @gender's defined (or ::gender in sub-class), returns
	 * a best guess of gender, based on last letter of the name.
	 *
	 * @return string
	 */
	protected function gender()
	{
		$default_gender = preg_match('/a$/', $this->name) ? 'f' : 'm';
		return $this->__gender ? $this->__gender : $default_gender;
	}

	/**
	 * protected boolean assign(string $var, $mixed $val)
	 *      Register variables by name and value, for the view to use.
	 *
	 * @param string $var
	 * @param mixed $val
	 * @return boolean
	 */
	public function assign($var, $val)
	{
		return oSmarty()->assign($var, $val);
	}

	/**
	 * protected string fetch(string $tpl)
	 *      Interpret and return a template's HTML, using assigned vars.
	 *
	 * @param string $tpl
	 * @return string
	 */
	public function fetch($tpl)
	{
		return oSmarty()->fetch($tpl);
	}

	/**
	 * array mapnames(array $list)
	 *      Receives a list of field names, returns a map "field name => alias".
	 *
	 * @param array $list
	 * @return array
	 */
	public function mapnames($list)
	{
		$fields = array();
		$scr = $this->__screen_names;

		foreach ($list as $item)
		{
			$fields[$item] = isset($scr[$item]) ? $scr[$item] : $item;
		}

		return $fields;
	}


/******************************************************************************/
/********************** A B S T R A C T   M E T H O D S ***********************/
/******************************************************************************/

	/**
	 * array getTabularData([mixed $limit = 30])
	 *      Generate relevant information to build a tabular list.
	 *
	 * @param mixed $limit      A valid LIMIT value (e.g. 4, '0, 30', etc.).
	 * @return array
	 */
	abstract public function getTabularData($limit=30);

	/**
	 * array getItemData(mixed $id)
	 *      Generate relevant information to build a single item's page.
	 *
	 * @param mixed $id         The id of this element (primary key value)
	 * @return array
	 */
	abstract public function getItemData($id);


/******************************************************************************/
/************************* M A G I C   M E T H O D S **************************/
/******************************************************************************/

	/**
	 * mixed __get()
	 *      Together with __set() makes all properties visible but readonly. For
	 * defined properties starting with "__", it will first attempt to call a
	 * method by that name but without the __ prefix.
	 *
	 * @param string $prop
	 * @return mixed
	 */
	public function __get($prop)
	{
		if (property_exists($this, "__{$prop}"))
		{
			if (is_callable(array($this, $prop)))
			{
				return $this->$prop();
			}
			else
			{
				return $this->{"__{$prop}"};
			}
		}
	}

	/**
	 * void __set()
	 *      Deny creation of undeclared properties.
	 *
	 * @param string $prop
	 * @param mixed $value
	 * @return void
	 */
	public function __set($prop, $value)
	{
		if (property_exists($this, "__{$prop}"))
		{
			trigger_error("Attempting to modify readonly property $prop", E_USER_WARNING);
		}
	}

}