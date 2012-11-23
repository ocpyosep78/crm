<?php


abstract class View
{

	private static $cache;

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
	 * final static View get(string $name[, string $namespace = 'global'])
	 *      Get from cache, or instantiate, a View for given $name (model).
	 *
	 * @param string $name
	 * @param string $namespace
	 * @return View subclass
	 */
	final public static function get($name, $namespace='global')
	{
		if (!$name)
		{
			$msg = "Trying to initialize a view without a view name";
			throw new Exception($msg);
		}

		if (!preg_match('/\w+/', $name))
		{
			$msg = "Illegal character in View name: {$name}";
			throw new Exception($msg);
		}

		$ucname = ucfirst($name);

		if (empty(self::$cache[$namespace][$ucname]))
		{
			$hierarchy = [];
			$class = 'View';

			foreach (explode('.', $name) as $file)
			{
				$parent = $class;
				$hierarchy[] = ucfirst($file);

				$path = APP_VIEWS . '/' . join('.', $hierarchy) . '.php';
				$class = 'View_' . join('', $hierarchy);

				if (!is_file($path))
				{
					eval("class {$class} extends {$parent}{}");
				}
				elseif (!@include $path)
				{
					$msg = "Failed to load class {$class} for View {$ucname}";
					throw new Exception($msg);
				}
				elseif (!class_exists($class))
				{
					$msg = "View class {$class} for {$ucname} was not found";
					throw new Exception($msg);
				}
			}

			// Create
			$View = new $class;

			// Cache
			self::$cache[$namespace][$ucname] = $View;

			// Link corresponding View
			$View->Model = Model::get($ucname, $namespace);
		}

		return self::$cache[$namespace][$ucname];
	}


/******************************************************************************/
/******************************* I N S T A N C E ******************************/
/******************************************************************************/

	/**
	 * Block descendants from being directly initialized, using final keyword.
	 */
	final public function __construct()
	{
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

		if (empty($fields))
		{
			$msg = "Cannot get tabular params without fields to select";
			throw new Exception($msg);
		}

		$fieldinfo = $this->Model->columns(array_keys($fields));

		return compact('fields', 'fieldinfo');

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

		if (empty($fields))
		{
			$msg = "Cannot get item data without fields to select";
			throw new Exception($msg);
		}

		$fieldinfo = $this->Model->columns(array_keys($fields));

		if ($id)
		{
			$data = $this->Model->setId($id)->select($fields)
			             ->find()->convert('row')->get();
		}
		else
		{
			$data = array_combine($fields, array_fill(0, count($fields), ''));
		}

		return compact('fields', 'fieldinfo', 'data');
	}

	/**
	 * array getHashData()
	 *      Generate relevant information to build a single item's page.
	 *
	 * @return array
	 */
	public function getHashData()
	{
		($field = $this->hash_field) || ($field = $this->descr_field);

		if (empty($field))
		{
			$msg = "Cannot get hash data without a field to select";
			throw new Exception($msg);
		}

		return $this->Model
			->select("{$this->Model->getPk()} AS '__id__'",
			         "{$field} AS 'val'")->order('val DESC')
			->find()->convert('col')->get();
	}

	/**
	 * array getRelated([string $id = NULL])
	 *      Get elements related to the current Model but not direct part of it.
	 * If $id is provided, return full info on that particular related element.
	 *
	 * @param string $id
	 * @return array
	 */
	public function getRelated($id=NULL)
	{


		$tabs = [
			['id' => 'id1', 'content' => 'content1', 'title' => 'Title 1'],
			['id' => 'id2', 'content' => 'content2', 'title' => 'Title 2'],
			['id' => 'id3', 'content' => 'content3', 'title' => 'Title 3'],
			['id' => 'id4', 'content' => 'content4', 'title' => 'Title 4'],
			['id' => 'id5', 'content' => 'content5', 'title' => 'Title 5'],
		];

		return $tabs;
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