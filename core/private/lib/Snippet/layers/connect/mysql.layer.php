<?php

require_once(dirname(__FILE__) . '/mysql.database.php');


class snp_Layer_mysql extends snp_Database_mysql
{

	private $tables;
	private $fields;
	private $shown;
	private $keys;
	private $FKs;

	private $search;            // Object containing filters, order, limit, etc.
	private $lastSearch;


	public function __construct($table=NULL)
	{
		parent::__construct();

		$this->schema = $this->params['db'];
		$table && ($this->table = $table);

		$this->initSearchObj();
	}


	public function select($fields)
	{
		if (!$this->seems('fields', $fields))
		{
			throw new Exception('Invalid parameter passed to filter()');
		}

		if ($fields === '*')
		{
			$this->search->select = array();
		}
		else
		{
			$this->search->select = array_merge($this->search->select, (array)$fields);
		}
	}

	public function where($filter)
	{
		if (!$this->seems('filter', $filter))
		{
			throw new Exception('Invalid parameter passed to filter()');
		}

		$this->search->where = array_merge($this->search->where, $filter);
	}

	public function order($order)
	{
		if (!$this->seems('order', $order))
		{
			throw new Exception('Invalid parameter passed to order()');
		}

		$this->search->order = $order;
	}

	public function limit($limit)
	{
		if (!$this->seems('limit', $limit))
		{
			throw new Exception('Invalid parameter passed to limit()');
		}

		$this->search->limit = $limit;
	}

	public function setId($id)
	{
		$key = $this->getPk();

		if (count($key) !== 1)
		{
			throw new Exception('Method setId can only be used with single primary keys');
		}

		$pk = array_shift($key);

		$field = "{$pk['sch1']}.{$pk['tbl1']}.{$pk['col1']}";

		// ::setId() resets filters, if any was set before
		$this->search->where = array($field => $id);
	}

	/**
	 * snp_Result find([mixed $filter][, array $fields][, string $order][, string $limit])
	 *
	 * @param mixed $filter     Id (int or string), or filter (see ::where)
	 * @param array $fields     List of fields for the query to include (select)
	 * @param string $order     Valid sql order, at least matching \b(asc|desc)$
	 * @param mixed $limit      Number or string, valid limit, e.g. 4 or '0, 20'
	 * @return snp_Result
	 */
	public function find()
	{
		// Apply received arguments (filters, listing fields, order, limit)
		$args = func_get_args();

		// Filters (id or dictionary filter)
		$filter = array_shift($args);

		if ($filter)
		{
			if ($this->seems('id', $filter))
			{
				$this->setId($filter);
			}
			elseif ($this->seems('filter', $filter))
			{
				$this->where($filter);
			}
			else
			{
				$msg = 'First parameter of find() is reserved for filters';
				throw new Exception($msg);
			}
		}

		// Remaining arguments can be fields, order or limit
		foreach ($args as $i => $arg)
		{
			if ($this->seems('fields', $arg))
			{
				$this->select($arg);
			}
			elseif ($this->seems('order', $arg))
			{
				$this->order($arg);
			}
			elseif ($this->seems('limit', $arg))
			{
				$this->limit($arg);
			}
			else
			{
				$msg = 'Cannot interpret parameter #' . ($i+2) . ' passed to find(): ' . var_export($arg, true);
				throw new Exception($msg);
			}
		}

		// After applying args, call internal _find method
		return $this->_find();
	}

	private function _find()
	{
		extract(get_object_vars($this->search));

		$sql = "SELECT {$this->fields()}\n" .
		       "FROM {$this->tables()}\n" .
		       "WHERE {$this->array2filter($where, 'AND', 'LIKE')}\n" .
		       ($order ? "ORDER BY {$order}\n" : '') .
		       ($limit ? "LIMIT {$limit}" : '');
		$res = $this->query($sql, 'named');

		// Create a new Result
		$Result = new snp_Result($this->search, $sql, $res, 'named');

		// Reset @search object
		$this->initSearchObj();

		return $Result;
	}

	/**
	 * private boolean seems(string $type, mixed $arg)
	 *      Answers the question "does $arg seem to be of type $type?"
	 *
	 * @param string $type
	 * @param mixed $arg
	 * @return boolean
	 */
	private function seems($type, $arg)
	{
		switch ($type)
		{
			case 'id':
				return (is_string($arg) || is_numeric($arg));
			case 'filter':
				return (is_array($arg) && !is_numeric(key($arg)));

			case 'fields':
				return (is_array($arg) && is_int(key($arg)))
				    || (is_string($arg) && preg_match('_^(\*|\w+)$_', $arg));

			case 'order':
				return (is_string($arg) && preg_match('_[^\w](asc|desc)$_i', $arg));
			case 'limit':
				return (is_numeric($arg))
				    || (is_string($arg) && preg_match('_^\d+( *, *\d+)?$_', trim($arg)));

			default:
				return false;
		}
	}

	// Create an empty @search object
	private function initSearchObj()
	{
		// Initialize @search
		$this->search = new stdClass;
		$this->search->select = array();
		$this->search->where = array();
		$this->search->order = '';
		$this->search->limit = 30;
	}

	/**
	 * private string fields()
	 *      From the pool of all available fields, create a valid sql for the
	 * SELECT part, with only the ones hat were picked (all if none was picked).
	 *
	 * @return string
	 */
	private function fields()
	{
		extract($this->read());

		$fields = array_merge($columns['own'], $columns['src']);

		foreach ($fields as $fqn => $c)
		{
			$col = $c['COLUMN_NAME'];
			$ord = $tables['ordinals']["{$c['TABLE_SCHEMA']}.{$c['TABLE_NAME']}"];

			$fieldsSql[] = "`t{$ord}`.`{$c['COLUMN_NAME']}` AS '{$fqn}'";
		}

		return empty($fieldsSql) ? array('*') : join(",\n       ", $fieldsSql);
	}

	/**
	 * private string tables()
	 *       Builds (and returns) the FROM part of the SELECT queries.
	 *
	 * @return string
	 */
	private function tables()
	{
		extract($this->read());

		foreach ($keys['src'] as $k)
		{
			$ord = $tables['ordinals']["{$k['sch2']}.{$k['tbl2']}"];

			$f1 = "`t0`.`{$k['col1']}`";
			$f2 = "`t{$ord}`.`{$k['col2']}`";

			$joins[] = "JOIN `{$k['sch2']}`.`{$k['tbl2']}` `t{$ord}` ON ({$f2} = {$f1})";
		}

		$sql = "`{$this->schema}`.`{$this->table}` `t0`\n" . join("\n", $joins);

		return $sql;
	}



















	public function feed($summary)
	{
		# We assume Source sends the right keys,
		# for this is internal and wouldn't be abused
		foreach ($summary as $k => $v)
		{
			$this->$k = $v;
		}

		// TEMP
		if (isset($summary['mainTable']))
		{
			$this->table = array_shift($summary['mainTable']);
		}

		return $this;
	}

}