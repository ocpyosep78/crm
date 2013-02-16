<?php

require_once dirname(__FILE__) . '/ModelView.php';


abstract class View
{

	/**
	 * Compendium of handy tools and global methods
	 */
	use Object;

	/**
	 * Models and Views are initialized exactly the same. For each namespace, there
	 * is a Singleton created by relying on static method get. These named instances
	 * are cached on a private static var, to be retrieved on subsequent calls.
	 *
	 * Since the procedure matches, the aforementioned static var and method come
	 * packed within a trait.
	 */
	use ModelView;

	public $Model;
	private $TplEngine;

	protected $__actions;       // Extra actions for this Model's items

	protected $__descr_field;   // The most descriptive field of the model
	protected $__img_path;      // Path to an item's image, with wildcard %id%

	protected $__screen_names = array();
	protected $__extended_fields = array();

	protected $__tabular_fields = array();
	protected $__fullinfo_fields = array();


/******************************************************************************/
/******************************* I N S T A N C E ******************************/
/******************************************************************************/

	/**
	 * Block descendants from being directly initialized, using final keyword.
	 */
	final public function __construct() {}

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
/************************ T E M P L A T E   E N G I N E ***********************/
/******************************************************************************/

	/**
	 * protected boolean assign(string $var, $mixed $val[, string $namespace = 'global'])
	 *      Register variables by name and value, for the view to use.
	 *
	 * @param string $var
	 * @param mixed $val
	 * @param string $namespace
	 * @return boolean
	 */
	public function assign($var, $val, $namespace='global')
	{
		return $this->getTplEngine($namespace)->assign($var, $val);
	}

	/**
	 * mixed retrieve(string $var[, string $namespace = 'global'])
	 *      Return the value of a previously set template var.
	 *
	 * @param string $var
	 * @param string $namespace
	 * @return mixed
	 */
	public function retrieve($var, $namespace='global')
	{
		return $this->getTplEngine($namespace)->getTemplateVars($var);
	}

	/**
	 * string fetch(string $tpl[, string $namespace = 'global'])
	 *      Interpret and return a template's HTML, using assigned vars.
	 *
	 * @param string $tpl
	 * @param string $namespace
	 * @return string
	 */
	public function fetch($tpl, $namespace='global')
	{
		$TplEngine = $this->getTplEngine($namespace);
		return Template::one()->createTemplate($tpl, $TplEngine)->fetch();
	}

	/**
	 * private SmartyData getTplEngine(string $namespace)
	 *     Return namespaced SmartyData, either cached or new.
	 *
	 * @param string $namespace
	 * @return SmartyData
	 */
	private function getTplEngine($namespace)
	{
		if (empty($this->TplEngine[$namespace]))
		{
			$TplEngine = Template::one()->createData();

			// Register common attributes for the view
			$TplEngine->assign('name',   $this->name);
			$TplEngine->assign('plural', $this->plural);
			$TplEngine->assign('gender', $this->gender);

			$TplEngine->assign('DEVMODE', devMode());

			$this->TplEngine[$namespace] = $TplEngine;
		}

		return $this->TplEngine[$namespace];
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

		// Only real fields are shown here (i.e. not composed fields)
		foreach ($fields as $k => $field)
		{
			if (!preg_match('_^(\w+|`\w+`)$_', $k))
			{
				unset($fields[$k]);
			}
		}

		$fieldinfo = $this->Model->columns(array_keys($fields));

		if ($id)
		{
			$data = $this->Model->setId($id)->select($fields)
			             ->find()->convert('row')->get();
		}
		else
		{
			$data = array_combine($fields, array_fill(0, count($fields), '')) +
			        ['__id__' => 0,
			         '__disabled__' => false,
			         '__description__' => ''];
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
		if (!$this->descr_field)
		{
			$msg = "Cannot get hash data without a field to select";
			throw new Exception($msg);
		}

		return $this->Model->order('__description__ ASC')->limit(0)
		            ->find()->convert('col')->get();
	}

	public function image($id)
	{
		if (!$this->img_path)
		{
			return NULL;
		}

		$path = str_replace('%id%', $id, $this->img_path);

		if (!is_readable($path))
		{
			$path = str_replace('%id%', '__missing__', $this->img_path);
		}

		return $path;
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