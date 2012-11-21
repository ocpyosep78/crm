<?php

require_once DATASOURCE . '/Model.php';


abstract class Model extends DS_Model
{

	private static $cache;

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
	 * final static Model get(string $name[, string $namespace = 'global'])
	 *      Get from cache, or instantiate, a Model for given $name.
	 *
	 * @param string $name
	 * @param string $namespace
	 * @return Model subclass
	 */
	final public static function get($name, $namespace='global')
	{
		if (!$name)
		{
			$msg = "Trying to initialize a model without a model name";
			throw new Exception($msg);
		}

		if (!preg_match('/\w+/', $name))
		{
			$msg = "Illegal character in Model name: {$name}";
			throw new Exception($msg);
		}

		$ucname = ucfirst($name);

		if (empty(self::$cache[$namespace][$ucname]))
		{
			$hierarchy = [];
			$class = 'Model';

			foreach (explode('.', $name) as $file)
			{
				$parent = $class;
				$hierarchy[] = ucfirst($file);

				$path = APP_MODELS . '/' . join('.', $hierarchy) . '.php';
				$class = 'Model_' . join('', $hierarchy);

				if (!is_file($path))
				{
					eval("class {$class} extends {$parent}{}");
				}
				elseif (!@include $path)
				{
					$msg = "Failed to load class {$class} for Model {$ucname}";
					throw new Exception($msg);
				}
				elseif (!class_exists($class))
				{
					$msg = "Model class {$class} for {$ucname} was not found";
					throw new Exception($msg);
				}
			}

			// Create
			$Model = new $class($name);

			// Cache
			self::$cache[$namespace][$ucname] = $Model;

			// Link corresponding View
			$Model->View = View::get($ucname, $namespace);
		}

		return self::$cache[$namespace][$ucname];
	}


/******************************************************************************/
/******************************* I N S T A N C E ******************************/
/******************************************************************************/

	/**
	 * Block descendants from being directly initialized, using final keyword.
	 */
	final public function __construct($name)
	{
		$this->__schema = DS_SCHEMA;
		$this->__table || ($this->__table = explode('.', $name)[0]);

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

}