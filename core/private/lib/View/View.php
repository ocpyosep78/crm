<?php


abstract class View
{

	private static $cached_views;

	protected $Model;
	private $TplEngine;

	protected $__descr_field;   // The most descriptive field of the model
	protected $__hash_field;    // Field representing the model (often composed)

	protected $__screen_names = array();
	protected $__extended_fields = array();

	protected $__tabular_fields = array();
	protected $__fullinfo_fields = array();


/******************************************************************************/
/********************************* S T A T I C ********************************/
/******************************************************************************/

	/**
	 * final static View get(string $viewname[, string $namespace = 'global'])
	 *      Get from cache, or instantiate, a View for given $viewname (model).
	 *
	 * @param string $viewname
	 * @param string $namespace
	 * @return View subclass
	 */
	final public static function get($viewname, $namespace='global')
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

		if (empty(self::$cached_views[$namespace][$ucview]))
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

				$View = new $class($viewname, $namespace);
			}
			else
			{
				require_once dirname(__FILE__) . '/AbstractView.php';
				$View = new AbstractView($ucview, $namespace);
			}
		}

		return self::$cached_views[$namespace][$ucview];
	}


/******************************************************************************/
/******************************* I N S T A N C E ******************************/
/******************************************************************************/

	/**
	 * Block descendants from being directly initialized, using final keyword.
	 */
	final public function __construct($viewname, $namespace)
	{
		self::$cached_views[$namespace][$viewname] = $this;

		$this->Model = Model::get($viewname, $namespace);

		$this->TplEngine = oSmarty()->createData();

		// Register common attributes for the view
		$this->assign('name',   $this->name);
		$this->assign('plural', $this->plural);
		$this->assign('gender', $this->gender);

		$this->assign('DEVMODE', devMode());
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
		return $this->TplEngine->assign($var, $val);
	}

	public function retrieve($var)
	{
		return $this->TplEngine->getTemplateVars($var);
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
		return oSmarty()->createTemplate($tpl, $this->TplEngine)->fetch();
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
/********************* L I N K   T O   T H E   M O D E L **********************/
/******************************************************************************/

	/**
	 * array getTabularParams()
	 *      Generate relevant information to build a tabular list.
	 *
	 * @return array
	 */
	public function getTabularParams()
	{
		$fields = $this->__tabular_fields
			? $this->mapnames($this->__tabular_fields)
			: $this->__screen_names;

		$fieldinfo = $this->Model->columns(array_keys($fields));

		$primary = $this->getPkAlias();

		return compact('fields', 'fieldinfo', 'primary');

	}

	/**
	 * array getItemData([mixed $id = NULL])
	 *      Generate relevant information to build a single item's page.
	 *
	 * @param mixed $id         The id of this element (primary key value)
	 * @return array
	 */
	public function getItemData($id=NULL)
	{
		$fields = $this->fullinfo_fields
			? $this->mapnames($this->fullinfo_fields)
			: $this->__screen_names;

		$fieldinfo = $this->Model->columns(array_keys($fields));

		if ($id)
		{
			$this->Model->setId($id)->select($fields);
			$data = $this->Model->find()->convert('row')->get();
		}
		else
		{
			$data = array();
		}

		$primary = $this->getPkAlias();

		return compact('fields', 'fieldinfo', 'data', 'primary');
	}

	/**
	 * array getHashData()
	 *      Generate relevant information to build a single item's page.
	 *
	 * @param mixed $id         The id of this element (primary key value)
	 * @return array
	 */
	public function getHashData()
	{
		($field = $this->hash_field) || ($field = $this->descr_field);

		return $this->Model
			->select('__id__', "{$field} AS 'val'")->order('val DESC')
			->find()->convert('col')->get();
	}

	public function getPkAlias()
	{
		$scr = $this->screen_names;
		$pk = $this->Model->getPk();

		return isset($scr[$pk]) ? $scr[$pk] : $pk;
	}


/******************************************************************************/
/************************* M A G I C   M E T H O D S **************************/
/******************************************************************************/


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