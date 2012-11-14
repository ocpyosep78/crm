<?php

require_once(CONNECTION_PATH);


class snp_Database_mysql extends Connection
{

	protected $schema;
	protected $table;


	final protected function getPk()
	{
		$structure = $this->read();
		return $structure['keys']['pri'];
	}

	final protected function read()
	{
		static $structure;

		if (!$structure)
		{
			$keys    = $this->getKeys($this->schema, $this->table);

			$tables  = $this->getTables($keys['all']);
			$columns = $this->getColumns($tables['all']);

			$structure = compact('keys', 'tables', 'columns');
		}

		return $structure;
	}

	private function getKeys($schema, $table)
	{
		$all = $pri = $own = $src = $tgt = array();

		$sql = "SELECT `CONSTRAINT_NAME` AS 'name',
		               `TABLE_SCHEMA` AS 'sch1',
		               `TABLE_NAME` AS 'tbl1',
		               `COLUMN_NAME` AS 'col1',
		               `REFERENCED_TABLE_SCHEMA` AS 'sch2',
		               `REFERENCED_TABLE_NAME` AS 'tbl2',
		               `REFERENCED_COLUMN_NAME` AS 'col2'
		        FROM `information_schema`.`key_column_usage`
		        WHERE (`TABLE_SCHEMA` = '{$schema}' AND `TABLE_NAME` = '{$table}')
		           OR (`REFERENCED_TABLE_SCHEMA` = '{$schema}' AND `REFERENCED_TABLE_NAME` = '{$table}')";
		$all = $this->query($sql, 'array');

		foreach ($all as &$k)
		{
			if ($k['name'] == 'PRIMARY')
			{
				$k['type'] = 'pri';
			}
			elseif (!$k['sch2'])
			{
				$k['type'] = 'unique';
			}
			elseif (($k['sch1'] == $k['sch2']) && ($k['tbl1'] == $k['tbl2']))
			{
				$k['type'] = 'own';
			}
			elseif (($k['sch1'] == $schema) && ($k['tbl1'] == $table))
			{
				$k['type'] = 'src';
			}
			else
			{
				$k['type'] = 'tgt';
			}

			${$k['type']}[] =& $k;
		}

		$keys = compact('all', 'pri', 'own', 'src', 'tgt');

		return $keys;
	}

	private function getTables($keys)
	{
		// Find out which tables are involved, to get their columns
		$all = $own = $src = $tgt = array();

		$ownKey = "{$this->schema}.{$this->table}";
		$all[$ownKey] = array('schema'  => $this->schema,
		                      'table'   => $this->table,
		                      'type' => array('own' => 'own'));

		$own[$ownKey] =& $all[$ownKey];

		foreach ($keys as $k)
		{
			// Fully qualified name and entry skeleton structure
			$fqn1 = "{$k['sch1']}.{$k['tbl1']}";
			$fqn2 = "{$k['sch2']}.{$k['tbl2']}";

			$t1 = array('schema' => $k['sch1'], 'table' => $k['tbl1'], 'type' => array());
			$t2 = array('schema' => $k['sch2'], 'table' => $k['tbl2'], 'type' => array());

			$k['tbl1'] && !isset($all[$fqn1]) && ($all[$fqn1] = $t1);
			$k['tbl2'] && !isset($all[$fqn2]) && ($all[$fqn2] = $t2);

			// Add the corresponding type to each table (src or tgt)
			if ($k['tbl1'] && ($k['type'] == 'tgt'))
			{
				$src[$fqn1] =& $all[$fqn1];
				$tgt[$fqn1]['type']['tgt'] = 'tgt';

				// Flag main table as tgt as well
				$tgt[$ownKey] =& $all[$ownKey];
				$all[$ownKey]['type']['tgt'] = 'tgt';
			}
			elseif ($k['tbl2'] && ($k['type'] == 'src'))
			{
				$tgt[$fqn2] =& $all[$fqn2];
				$tgt[$fqn2]['type']['src'] = 'src';

				// Flag main table as src as well
				$src[$ownKey] =& $all[$ownKey];
				$all[$ownKey]['type']['src'] = 'src';
			}
		}

		$ordinals = array_flip(array_keys($all));

		$tables = compact('all', 'own', 'src', 'tgt', 'ordinals');

		return $tables;
	}

	private function getColumns($tables)
	{
		// Prepare sql to filter query in search of all relevant columns
		foreach ($tables as $t)
		{
			$cond[] = "(`TABLE_SCHEMA` = '{$t['schema']}' AND `TABLE_NAME` = '{$t['table']}')";
		}

		$condition = empty($cond) ? 0 : join(' OR ', $cond);

		// Get the structure and candidate columns of each listed table
		$sql = "SELECT *
				FROM `information_schema`.`columns`
				WHERE {$condition}";
		$raw = $this->query($sql, 'array');

		foreach ($raw as $c)
		{
			$schema = $c['TABLE_SCHEMA'];
			$table = $c['TABLE_NAME'];
			$field = $c['COLUMN_NAME'];

			$tbl_fqn = "{$schema}.{$table}";
			$col_fqn = "{$schema}.{$table}.{$field}";

			$all[$col_fqn] = $c;

			foreach (array('own', 'src', 'tgt') as $type)
			{
				if (in_array($type, $tables[$tbl_fqn]['type']))
				{
					$all[$col_fqn]['tbl_type'][$type] = $type;
					${$type}[$col_fqn] =& $all[$col_fqn];
				}
			}
		}

		$columns = compact('all', 'own', 'src', 'tgt');

		return $columns;
	}

}




class snp_Result
{

	private $__search;      // Query parameters (filters, limit, order, etc.)
	private $__query;       // The sql query
	private $__dataset;     // Result (migth be formatted)
	private $__datatype;    // Result format (array, named, row, col, res, ...)


	public function __construct($search, $query, $dataset, $datatype)
	{
		$this->__search = $search;
		$this->__query = $query;
		$this->__dataset = $dataset;
		$this->__datatype = $datatype;
	}

	/**
	 * array flat()
	 *      Remove schema and table namespaces from result sets. This might
	 * mean that some fields will be overwritten, if called the same.
	 *
	 * @return array
	 */
	public function flat()
	{
		static $flat;

		if (is_null($flat))
		{
			switch ($this->datatype)
			{
				case 'array':
				case 'named':
					foreach ($this->dataset as $row_no => $row)
					{
						foreach ($row as $field => $val)
						{
							$flat[$row_no][end(explode('.', $field))] = $val;
						}
					}
					break;

				default: return $this->dataset;
			}
		}

		return $flat;
	}


	/**
	 * Magic method __get()
	 *      Together with __set() makes all properties visible but readonly.
	 *
	 * @param string $prop
	 * @return mixed
	 */
	public function __get($prop)
	{
		if (property_exists($this, "__{$prop}"))
		{
			return $this->{"__{$prop}"};
		}
	}

	/**
	 * Magic method __set()
	 *      Deny creation of undeclared properties.
	 *
	 * @param string $prop
	 * @param mixed $value
	 */
	public function __set($prop, $value)
	{
		if (property_exists($this, "__{$prop}"))
		{
			trigger_error("Attempting to modify readonly property $prop", E_USER_WARNING);
		}
	}

}