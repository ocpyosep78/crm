<?php

require_once(DATASOURCE . '/Connect.php');
require_once(DATASOURCE . '/Result.php');
require_once(DATASOURCE . '/Structure.php');



abstract class DS_Model extends DS_Structure
{

	private $search;    // Object storing current select, where, order and limit


	public function __construct()
	{
		// Test integrity of object definitions
		if (!$this->__table || !$this->__schema)
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
	 * DS_Model select(mixed $select)
	 *      Choose which fields to retrieve in next query.
	 *
	 * @param mixed $select
	 * @return DS_Model
	 */
	public function select($select)
	{
		if (!$this->seems('select', $select))
		{
			$msg = 'Invalid parameter passed to select(): ' . var_export($select, true);
			throw new Exception($msg);
			throw new Exception('Invalid parameter passed to select()');
		}

		if (!is_array($select))
		{
			if ((count(func_get_args()) === 1) && strpos($select, ','))
			{
				$select = preg_split('/ *, */', $select);
			}
			else
			{
				$select = func_get_args();
			}
		}

		foreach ($select as $k => $v)
		{
			if (is_numeric($k))
			{
				// E.g. ::select('c1 as col1'), select(["`c2` as 'col2'"])
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

		// Keywords to replace for the real field they represent
		$keywords = ['__id__' => $this->getPk()];

		// Do it this way to preserve select order
		foreach ($cols as $col => $alias)
		{
			isset($keywords[$col]) && ($col = $keywords[$col]);
			$new[$col] = $alias;
		}

		if (isset($new))
		{
			$this->search->select = array_merge($this->search->select, $new);
		}

		return $this;
	}

	public function where($where)
	{
		if (!$this->seems('where', $where))
		{
			$msg = 'Invalid parameter passed to where(): ' . var_export($where, true);
			throw new Exception($msg);
		}

		$this->search->where = array_merge($this->search->where, (array)$where);

		return $this;
	}

	public function order($order)
	{
		if (!$this->seems('order', $order))
		{
			$msg = 'Invalid parameter passed to order(): ' . var_export($order, true);
			throw new Exception($msg);
		}

		$this->search->order = $order;

		return $this;
	}

	public function limit($limit)
	{
		if (!$this->seems('limit', $limit))
		{
			$msg = 'Invalid parameter passed to limit(): ' . var_export($limit, true);
			throw new Exception($msg);
		}

		$this->search->limit = $limit;

		return $this;
	}

	public function setId($id)
	{
		$pk = $this->getPk();

		if (!$pk)
		{
			$msg = "setId() failed: cannot find a PK for Model's main table";
			throw new Exception($msg);
		}

		// ::setId() resets filters, if any was set before
		$primary = "`{$this->schema}`.`{$this->table}`.`{$pk}`";
		$this->search->where = [$primary => $id];

		return $this;
	}

	/**
	 * Model find([mixed $where][, array $select][, string $order][, string $limit])
	 *
	 * @param mixed $where      Id (int or string), or where (see ::where)
	 * @param array $select     Select for the query to include (see ::select)
	 * @param string $order     Valid sql order, at least matching \b(asc|desc)$
	 * @param mixed $limit      Number or string, valid limit, e.g. 4 or '0, 20'
	 * @return snp_Result
	 */
	public function find()
	{
		// Apply received arguments (where, select, order, limit)
		$args = func_get_args();

		// Filters (id or dictionary filter)
		$where = array_shift($args);

		if ($where)
		{
			if ($this->seems('id', $where))
			{
				$this->setId($where);
			}
			elseif ($this->seems('where', $where))
			{
				$this->where($where);
			}
			else
			{
				$msg = 'First parameter of find() is reserved for filters';
				throw new Exception($msg);
			}
		}

		// Remaining arguments can be select, order or limit
		foreach ($args as $i => $arg)
		{
			if ($this->seems('limit', $arg))
			{
				$this->limit($arg);
			}
			elseif ($this->seems('order', $arg))
			{
				$this->order($arg);
			}
			elseif ($this->seems('select', $arg))
			{
				$this->select($arg);
			}
			elseif (!is_null($arg) && ($arg !== '') && ($arg !== []))
			{
				$msg = 'Cannot interpret parameter #' . ($i+2) . ' passed to find(): ' . var_export($arg, true);
				throw new Exception($msg);
			}
		}

		// Now import @search keys to this scope, and let's execute the query
		extract(get_object_vars($this->search));

		if (empty($select))
		{
			db(debug_print_backtrace());
			$msg = "Cannot execute a query without elements to select";
			throw new Exception($msg);
		}

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
		$Result = new DS_Result($this->Answer, $this->search, $sql, $res, $this);

		// Reset @search object
		$this->initSearchObj();

		return $Result;
	}

	/**
	 * object update(array $set, mixed $filter)
	 *      Update elements from the main table, where $filter applies.
	 *
	 * TODO : for now it just accepts IDs as filter. It should accept any filter
	 *        that where() accepts.
	 *
	 * @param array $set            Array of "field => newValue" pairs
	 * @param mixed $filter
	 * @return object               Answer object (stdClass)
	 */
	public function update($set, $filter)
	{
		if (!is_array($set))
		{
			$msg = 'Call to update() failed: $set must be an array';
			throw new Exception($msg);
		}

		if (is_array($filter))
		{
			$msg = "Call to update() failed: only IDs are accepted as filters";
			throw new Exception($msg);
		}

		foreach ($set as $k => $v)
		{
			$sets[] = "`{$k}` = '{$v}'";
		}

		$id = $filter;
		$assignments = join(', ', $sets);

		$sql = "UPDATE `{$this->schema}`.`{$this->table}`
		        SET {$assignments}
		        WHERE `{$this->getPk()}` = '{$id}'";

		return $this->query($sql);
	}

	/**
	 * object delete(mixed $filter)
	 *      Delete elements from the main table, where $filter applies.
	 *
	 * TODO : for now it just accepts IDs as filter. It should accept any filter
	 *        that where() accepts.
	 *
	 * @param mixed $filter
	 * @return object               Answer object (stdClass)
	 */
	public function delete($filter)
	{
		if (is_array($filter))
		{
			$msg = "Call to delete() failed: only IDs are accepted as filters";
			throw new Exception($msg);
		}

		$id = $filter;

		$sql = "DELETE FROM `{$this->schema}`.`{$this->table}`
		        WHERE `{$this->getPk()}` = '{$id}'";
		return $this->query($sql);
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
		if (($arg === []) || ($arg === ''))
		{
			return false;
		}

		switch ($type)
		{
			case 'id':
				return is_numeric($arg);

			case 'where':
				// Valid strings: field1 <compare> field2 ...
				$regex1 = "^(`\w+`|\w+) +([<>]|[<>!]*=|<>|IS( +NOT)?|(NOT +)?LIKE) +(['\"].+['\"]|(\d|NULL)+)\$";
				// ... field IN(list), field BETWEEN X AND Y ...
				$regex2 = '.+ (BETWEEN .+ AND .+|(NOT +)?IN *\(.+\))$';
				// ... field, NOT field, IS NULL field, IS NOT NULL field
				$regex3 = '^((NOT|IS ?NULL|IS NOT NULL)( +| *\())? *(\w+\.|`\w+`\.){0,2}(\w+|`\w+`) *\)?$';

				return (is_string($arg) && preg_match("_({$regex1})|({$regex2})|({$regex3})_i", trim($arg))) || (is_array($arg));

			case 'select':
				$regex = '_^(\*|[\w ]+(, *[\w ]+)+|(`\w+`|\w+)( +as [\'"]?\w+[\'"]?)?)$_i';
				return (is_string($arg) && !is_numeric($arg) && preg_match($regex, trim($arg))) || (is_array($arg));

			case 'order':
				$regex = '_\b(asc|desc)$_i';
				return ($arg && is_string($arg) && preg_match($regex, $arg));

			case 'limit':
				$regex = '_^\d+( *, *\d+)?$_';
				return (is_numeric($arg)) || (is_string($arg) && preg_match($regex, trim($arg)));

			default:
				return false;
		}
	}

	// Create an empty @search object
	private function initSearchObj()
	{
		// Initialize @search
		$this->search = new stdClass;
		$this->search->select = [];
		$this->search->where = [];
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

			$fieldsSql[] =  "{$fqn} AS '" . addslashes($alias) . "'";
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
			elseif (!is_numeric($k) && $v && (is_array($v) || strpos($v, ',')))
			{
				$list = is_array($v)
					? join(', ', array_map('addslashes', $v))
					: addslashes($v);

				$cond[] = "{$k} IN ('{$list}')";
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