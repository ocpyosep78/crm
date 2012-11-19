<?php

require_once(DATASOURCE . '/Connect.php');
require_once(DATASOURCE . '/Result.php');
require_once(DATASOURCE . '/Structure.php');



abstract class DS_Model extends DS_Structure
{

	private $tables;
	private $fields;
	private $shown;
	private $keys;
	private $FKs;

	private $search;            // Object containing filters, order, limit, etc.
	private $lastSearch;


	public function __construct()
	{
		// Test integrity of object definitions
		if (empty($this->table) || empty($this->schema))
		{
			$msg = "A Model is required to have a @table and a @schema";
			throw new Exception($msg);
		}

		extract($this->read());

		if (empty($columns))
		{
			$msg = "DataSource failed to initialize with table {$this->table} and schema {$this->schema}";
			throw new Exception($msg);
		}

		$this->initSearchObj();
	}


	/**
	 *
	 * @param mixed $fields
	 * @return DS_Model
	 */
	public function select($fields)
	{
		if (!$this->seems('fields', $fields))
		{
			throw new Exception('Invalid parameter passed to select()');
		}

		if (!is_array($fields))
		{
			if ((count(func_get_args()) === 1) && strpos($fields, ','))
			{
				$fields = preg_split('/ *, */', $fields);
			}
			else
			{
				$fields = func_get_args();
			}
		}

		foreach ($fields as $k => $v)
		{
			if (is_numeric($k))
			{
				// E.g. ::select('c1 as col1'), select(array("`c2` as 'col2'"))
				if (preg_match('_(.+) +as +([\'"][^\'"]+[\'"]|[^\'"]+)_i', trim($v), $matches))
				{
					$cols[trim($matches[1], '\'" ')] = trim($matches[2], '\'" ');
				}
				// E.g. ::select('col1', 'col2', ...)
				elseif (preg_match('_[^\w\.]_', trim($v, ' `\'"')))
				{
					$msg = 'Complex fields and non-alphanumeric field names are required to have an alias.';
					throw new Exception($msg);
				}
				else
				{
					$cols[$v] = trim(trim($v, '\'" `'));
				}
			}
			// E.g. ::select('c1' => 'col1', 'c2' => 'col2', ...)
			elseif (preg_match('_\w+_', trim($v, '\'" ')))
			{
				$cols[$k] = trim($v, '\'" ');
			}
			else
			{
				$msg = "Aliases can only contain alphanumeric characters.";
				throw new Exception($msg);
			}
		}

		$this->search->select = ($cols + $this->search->select);

		return $this;
	}

	public function where($filter)
	{
		if (!$this->seems('filter', $filter))
		{
			throw new Exception('Invalid parameter passed to where()');
		}

		$this->search->where = array_merge($this->search->where, (array)$filter);

		return $this;
	}

	public function order($order)
	{
		if (!$this->seems('order', $order))
		{
			throw new Exception('Invalid parameter passed to order()');
		}

		$this->search->order = $order;

		return $this;
	}

	public function limit($limit)
	{
		if (!$this->seems('limit', $limit))
		{
			throw new Exception('Invalid parameter passed to limit()');
		}

		$this->search->limit = $limit;

		return $this;
	}

	public function setId($id)
	{
		$key = $this->getPk();

		if (count($key) !== 1)
		{
			throw new Exception('Method setId can only be used with single primary keys');
		}

		$field = trim(end(explode('`.`', array_shift($key))), '`');

		// ::setId() resets filters, if any was set before
		$primary = "`{$this->schema}`.`{$this->table}`.`{$field}`";
		$this->search->where = array($primary => $id);

		return $this;
	}

	/**
	 * Model find([mixed $filter][, array $fields][, string $order][, string $limit])
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
			elseif (!is_null($arg))
			{
				$msg = 'Cannot interpret parameter #' . ($i+2) . ' passed to find(): ' . var_export($arg, true);
				throw new Exception($msg);
			}
		}

		// Now import @search keys to this scope, and let's execute the query
		extract(get_object_vars($this->search));

		$sql = "SELECT {$this->selectSql()}\n" .
		       "FROM {$this->joinSql()}\n" .
		       "WHERE {$this->whereSql()}\n" .
		       ($order ? "ORDER BY {$order}\n" : '') .
		       ($limit ? "LIMIT {$limit}" : '');
		$res = $this->query($sql);

		if ($res === false)
		{
			throw new Exception($sql . "\n" . mysql_error());
		}

		// Create a new DS_Result
		$Result = new DS_Result($this->search, $sql, $res, $this);

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
	protected function seems($type, $arg)
	{
		switch ($type)
		{
			case 'id':
				return is_numeric($arg);

			case 'filter':
				// Valid strings: X <compare> Y, X IN(list), X BETWEEN Y AND Z
				$operators = '[<>]|[<>!]*=|<>|IS( +NOT)?|(NOT +)?LIKE';
				$regex1 = "^(`\w+`|\w+) +({$operators}) +(['\"].+['\"]|(\d|NULL)+)\$";
				$regex2 = '.+ (BETWEEN .+ AND .+|IN *\(.+\))$';
				$regex = "_({$regex1})|({$regex2})_i";
				return (is_string($arg) && (preg_match($regex, trim($arg)) !== false))
					|| (is_array($arg));

			case 'fields':
				$regex = '_^(\*|[\w ]+(, *[\w ]+)+|(`\w+`|\w+)( +as [\'"]?\w+[\'"]?)?)$_i';
				return (is_string($arg) && !is_numeric($arg) && (preg_match($regex, trim($arg)) !== false))
				    || (is_array($arg));

			case 'order':
				$regex = '_[^\w](asc|desc)$_i';
				return (is_string($arg) && (preg_match($regex, $arg) !== false));

			case 'limit':
				$regex = '_^\d+( *, *\d+)?$_';
				return (is_numeric($arg))
				    || (is_string($arg) && (preg_match($regex, trim($arg)) !== false));

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
	 * private string selectSql()
	 *      From the pool of all available fields, create a valid sql for the
	 * SELECT part, with only the ones hat were picked (all if none was picked).
	 *
	 * @return string
	 */
	private function selectSql()
	{

		$resolved = $this->columns(array_keys($this->search->select));

		foreach ($resolved as $k => $c)
		{
			$key = isset($c['fqn']) ? $c['fqn'] : $k;
			$fields[$key] = $this->search->select[$k];
		}

		extract($this->read());

		foreach ($fields as $fqn => $alias)
		{
			if (isset($columns['all'][$fqn]))
			{
				$col = $columns['all'][$fqn]['column'];
				($alias != $col) || ($alias = $fqn);
			}

			$fieldsSql[] =  "{$fqn} AS '{$alias}'";
		}

		$sql = empty($fieldsSql) ? '*' : join(",\n       ", $fieldsSql);

		return $sql;
	}

	/**
	 * private string joinSql()
	 *       Builds (and returns) the FROM part of the SELECT queries.
	 *
	 * @return string
	 */
	private function joinSql()
	{
		extract($this->read());

		foreach ($keys['src'] as $k)
		{
			$f1 = "`{$this->schema}`.`{$this->table}`.`{$k['col1']}`";
			$f2 = "`{$k['sch2']}`.`{$k['tbl2']}`.`{$k['col2']}`";

			$joins[] = "LEFT JOIN `{$k['sch2']}`.`{$k['tbl2']}` ON ({$f2} = {$f1})";
		}

		$sql = "`{$this->schema}`.`{$this->table}`\n" . join("\n", $joins);

		return $sql;
	}

	private function whereSql()
	{
		// Process filters to build the SQL filter string, and return it
		foreach ($this->search->where as $k => $v)
		{
			// Accept both NULL and string 'null' (case insensitive)
			if (is_null($v) || (is_string($v) && strtolower($v) === 'null'))
			{
				$cond[] = "ISNULL({$k})";
			}
			// Lists of values (IN)
			elseif (!is_numeric($k) && (is_array($v) || strpos($v, ',')))
			{
				$list = is_array($v) ? join(', ', $v) : $v;
				$cond[] = "{$k} IN ({$list})";
			}
			// Literal
			elseif (is_numeric($k) && is_string($v) && !is_numeric($v) && trim($v))
			{
				$cond[] = trim($v);
			}
			elseif ((is_string($v) || is_numeric($v)) && trim($v))
			{
				$cond[] = "{$k} = '" . trim($v, " '") . "'";
			}
			else
			{
				throw new Exception("Invalid parameter passed to whereSql(): {$k} => {$v}");
			}
		}

		return isset($cond) ? join(" AND ", $cond) : '1';
	}

}