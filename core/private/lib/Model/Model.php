<?php

require_once DATASOURCE . '/Model.php';


abstract class Model extends DS_Model
{

	private static $cached_models;


/******************************************************************************/
/********************************* S T A T I C ********************************/
/******************************************************************************/

	final public static function get($modelname, $namespace='global')
	{
		if (!$modelname)
		{
			$msg = "Trying to initialize a model without a model name";
			throw new Exception($msg);
		}

		$ucmodel = ucfirst($modelname);

		if (!preg_match('/\w+/', $modelname))
		{
			$msg = "Illegal character in Model name: {$ucmodel}";
			throw new Exception($msg);
		}

		if (empty(self::$cached_models[$namespace][$ucmodel]))
		{
			$path = APP_MODELS . "/{$ucmodel}.php";

			if (is_file($path))
			{
				require_once $path;
				$class = "Model_{$ucmodel}";

				if (!class_exists($class))
				{
					$msg = "Model class for {$ucmodel} not found";
					throw new Exception($msg);
				}

				$Model = new $class($modelname);
			}
			else
			{
				require_once dirname(__FILE__) . '/AbstractModel.php';
				$Model = new AbstractModel($modelname);
			}

			self::$cached_models[$namespace][$modelname] = $Model;
		}

		return self::$cached_models[$namespace][$modelname];
	}


/******************************************************************************/
/******************************* I N S T A N C E ******************************/
/******************************************************************************/

	/**
	 * Block descendants from being directly initialized, using final keyword.
	 */
	final public function __construct($table)
	{
		$this->schema = DS_SCHEMA;
		!$this->table && $table && ($this->table = $table);

		parent::__construct();
	}


/******************************************************************************/
/************************* M A G I C   M E T H O D S **************************/
/******************************************************************************/

	/**
	 * mixed __get()
	 *      Together with __set() makes all properties visible but readonly. For
	 * defined properties starting with "__", it will first attempt to call a
	 * method by that name but without the __ prefix. Only if that method does
	 * not exist (or is not callable), then it returns the property's value.
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