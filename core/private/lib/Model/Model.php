<?php

require_once DATASOURCE . '/Model.php';


abstract class Model extends DS_Model
{

	private static $cached_models;

	protected $View;

	/**
	 * attempt (default)
	 *    Attempts to remove entry
	 *    Fails silently (returns an Answer object with all relevant info)
	 *
	 * delete
	 *    Attempts to remove entry, on failure set @delete_flag_field to 1
	 *    Throws Exception if @delete_flag_field is not defined
	 *    Throws Exception if it cannot delete nor flag the entry/entries
	 *
	 * force
	 *    Attempts to remove entry, agressively resolve constraints (TODO)
	 *    Throws Exception if it cannot resolve/cancel/remove all constraints
	 *
	 * disable
	 *    Sets @delete_flag_field := 1
	 *    Throws Exception if @delete_flag_field is not defined
	 *    Throws Exception if it cannot update @delete_flag_field
	 *
	 * Delete and disable throw an Exception an if @delete_flag_field is not set
	 * All fail silently if constraints prevent deletion (returns Answer), but
	 *
	 */
	protected $delete_strategy = 'attempt';
	protected $delete_flag_field = NULL;


/******************************************************************************/
/********************************* S T A T I C ********************************/
/******************************************************************************/

	/**
	 * final static Model get(string $modelname[, string $namespace = 'global'])
	 *      Get from cache, or instantiate, a Model for given $modelname.
	 *
	 * @param string $modelname
	 * @param string $namespace
	 * @return Model subclass
	 */
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

				$Model = new $class($ucmodel, $namespace);
			}
			else
			{
				require_once dirname(__FILE__) . '/AbstractModel.php';
				$Model = new AbstractModel($ucmodel, $namespace);
			}
		}

		return self::$cached_models[$namespace][$ucmodel];
	}


/******************************************************************************/
/******************************* I N S T A N C E ******************************/
/******************************************************************************/

	/**
	 * Block descendants from being directly initialized, using final keyword.
	 */
	final public function __construct($modelname, $namespace)
	{
		self::$cached_models[$namespace][$modelname] = $this;

		$this->View = View::get($modelname, $namespace);

		$this->schema = DS_SCHEMA;
		!$this->table && $table && ($this->table = $table);

		parent::__construct();
	}

	/**
	 * DS_Result find()
	 *      Make all lookups ignore rows flagged as 'deleted' (disabled), except
	 * when in devMode().
	 *
	 * @return DS_Result
	 */
	public function find()
	{
		if ($this->delete_flag_field)
		{
			if (!devMode())
			{
				$this->where("NOT {$this->delete_flag_field}");
			}
			else
			{
				$this->select("{$this->delete_flag_field} AS __disabled__");
			}
		}

		$args = func_get_args() + array(NULL, NULL, NULL, NULL);

		return parent::find($args[0], $args[1], $args[2], $args[3]);
	}

	/**
	 * DS_Result delete(mixed $id)
	 *      Given a delete strategy (see @delete_strategy), attempt to either
	 * delete the entry, flag it 'disabled', or both.
	 *
	 * @param mixed $id
	 * @return DS_Result
	 */
	public function delete($id)
	{
		$strategy = strtolower($this->delete_strategy);
		$flagField = $this->delete_flag_field;

		// Make sure required properties are set and valid
		if (in_array($strategy, array('delete', 'disable')))
		{
			if (!$flagField)
			{
				$msg = "Delete strategy '{$strategy}' requires @delete_flag_field to be set";
				throw new Exception($msg);
			}
			elseif (array_search($flagField, $this->model_cols) === false)
			{
				$msg = "Current @delete_flag_field {$flagField} was not found in main table";
				throw new Exception($msg);
			}
		}

		// Validate input ($id)
		if (!$id || !is_string($id))
		{
			$msg = 'Call to User::delete() failed: received $id is invalid';
			throw new Exception($msg);
		}

		// Attempt delete if in place
		if (in_array($strategy, array('attempt', 'delete', 'force')))
		{
			$Delete = parent::delete($id);
		}

		// From the result of the deletion, finish the procedure as suitable
		switch ($strategy)
		{
			default:
			case 'attempt':
				return $Delete;

			case 'force':
				$msg = 'Delete strategy "force" is not implemented yet';
				throw new Exception($msg);

			case 'disable':
				return parent::update(array($flagField => 1), $id);

			case 'delete':
				return $Delete->failed
					? parent::update(array($flagField => 1), $id)
					: $Delete;
		}
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