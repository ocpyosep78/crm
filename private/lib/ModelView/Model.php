<?php

require_once dirname(__FILE__) . '/ModelView.php';


abstract class Model extends Datasource
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

	private static $cache;

	public $View;

	protected $__implicit_select = NULL;
	protected $__implicit_where = NULL;

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
	 */
	protected $delete_strategy = 'attempt';
	protected $delete_flag_field = NULL;


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
		$args = func_get_args() + [NULL, NULL, NULL, NULL];

		// First param is reserved for filters
		$this->where(array_shift($args));

		// Apply current select, before adding implicit selects (to keep order)
		foreach ($args as &$arg)
		{
			if ($arg && $this->seems('select', $arg))
			{
				$this->select($arg);
				$arg = NULL;
			}
		}

		// Add defined implicit SELECT fields
		if ($implicit_select = $this->implicit_select) # assignment
		{
			foreach ((array)$implicit_select as $is)
			{
				$select = $this->mapAliases($is);
				$this->select(($select === $is) ? $select : [$select => $is]);
			}
		}

		// Add defined implicit WHERE conditions
		if ($implicit_where = $this->implicit_where) # assignment
		{
			foreach ((array)$implicit_where as $iw)
			{
				$this->where($this->mapAliases($iw));
			}
		}

		// Add built-in pseudo-fields to select
		foreach (['__id__', '__description__', '__disabled__'] as $iis)
		{
			$this->select([$this->mapAliases($iis) => $iis]);
		}

		// Hide "deleted" fields (those flagged as removed) for all but devs
		if ($this->delete_flag_field && !devMode())
		{
			$this->where("NOT {$this->delete_flag_field}");
		}

		return parent::find(NULL, $args[0], $args[1], $args[2]);
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
			elseif (array_search($flagField, $this->model_cols, true) === false)
			{
				$msg = "Current @delete_flag_field {$flagField} was not found in main table";
				throw new Exception($msg);
			}
		}

		$condition = $this->mapAliases(['__id__' => $id]);

		// Attempt delete if in place
		if (in_array($strategy, array('attempt', 'delete', 'force')))
		{
			$Delete = parent::delete($condition);
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
				return parent::update([$flagField => 1], $condition);

			case 'delete':
				return $Delete->failed
					? parent::update([$flagField => 1], $condition)
					: $Delete;
		}
	}

	/**
	 * DS_Result restore(mixed $id)
	 *      Given a soft-deleted item (i.e. flagged deleted but still in db),
	 * set its disabled field back to zero, effectively undeletting it.
	 *
	 * @param mixed $id
	 * @return DS_Result
	 */
	public function restore($id)
	{
		$flagField = $this->delete_flag_field;

		if (!$flagField)
		{
			$msg = "A @delete_flag_field is required in order to restore items";
			throw new Exception($msg);
		}
		elseif (array_search($flagField, $this->model_cols, true) === false)
		{
			$msg = "Current @delete_flag_field {$flagField} was not found in main table";
			throw new Exception($msg);
		}

		$where = $this->mapAliases(['__id__' => $id]);
		$Answer = parent::update(array($flagField => 0), $where);

		return $Answer;
	}

	/**
	 * protected mixed mapAliases(mixed $item)
	 *      Resolves pseudo-fields (e.g. __id__) to their real values. If item
	 * is a string, it maps it directly. If it's an array, however, it attempts
	 * to map the keys instead.
	 *
	 * @param mixed $item
	 * @return mixed
	 */
	protected function mapAliases($item)
	{
		$del_flag = $this->delete_flag_field;
		$description = $this->View->descr_field;

		$map = ['__id__'          => $this->getPk(),
		        '__description__' => $description ? $description : "''",
		        '__disabled__'    => $del_flag ? $del_flag : 0];

		if (is_array($item))
		{
			foreach ($item as $k => $v)
			{
				isset($map[$k]) ? ($mapped[$map[$k]] = $v) : ($mapped[$k] = $v);
			}
		}
		elseif ($item && isset($map[$item]))
		{
			$mapped = $map[$item];
		}
		else
		{
			$mapped = $item;
		}

		return $mapped;
	}

}