<?php

require_once(CONNECTION_PATH);


class SnippetLayer_mysql extends Connection
{

	private $mainTable;
	private $tables;
	private $fields;
	private $shown;
	private $keys;
	private $FKs;

	private $schema;
	private $table;


	public function __construct()
	{
		parent::__construct();
		$this->schema = $this->params['db'];
	}


	public function delete($table, $filters)
	{
		return parent::delete($table, $filters);
	}

	public function update($data, $table, $keys)
	{
		return parent::update($data, $table, $keys);
	}

	public function feed($summary)
	{
		# We assume Source sends the right keys,
		# for this is internal and wouldn't be abused
		foreach ($summary as $k => $v)
		{
			$this->$k = $v;
		}

		return $this;
	}






	  //////////////////////////////////////////////////////////
	 //////////////////////// TESTING /////////////////////////
	//////////////////////////////////////////////////////////

	public function setTable($table)
	{
		$this->table = $table;
		return $this;
	}

	public function generate($what=NULL)
	{
		switch ($what)
		{
			case 'list':
			default:
				return $this->sql4List();
		}
	}

	private function sql4List()
	{
		$fieldsSql = $this->getFieldsSql();
		$tablesSql = $this->getTablesSql();

		$sql = "SELECT {$fieldsSql}\n" .
               "FROM {$tablesSql}";

		return $sql;
	}

	private function getFieldsSql()
	{
		$structure = $this->getStructure();
		$columns = $structure['columns'];

		$tables = array_flip($structure['tables']['all']);
		$fields = array_merge($columns['main'], $columns['to']);

		foreach ($fields as $field)
		{
			$parts = explode('.', $field);
			$name = $parts[2];
			$t = $tables["{$parts[0]}.{$parts[1]}"];

			$fieldsSql[] = "`t{$t}`.`{$name}` AS '{$field}'";
		}

		return empty($fieldsSql) ? array('*') : join(",\n       ", $fieldsSql);
	}

	private function getTablesSql()
	{
		$structure = $this->getStructure();

		$flipped = array_flip($structure['tables']['all']);

		foreach ($structure['keys']['byCol'] as $col => $key)
		{
			if (in_array($col, $structure['columns']['to']))
			{
				$schema = $key['REFERENCED_TABLE_SCHEMA'];
				$table = $key['REFERENCED_TABLE_NAME'];
				$t = $flipped["{$schema}.{$table}"];

				$f1 = "`t0`.`{$key['COLUMN_NAME']}`";
				$f2 = "`t{$t}`.`{$key['REFERENCED_COLUMN_NAME']}`";

				$joins[] = "JOIN `{$table}` `t{$t}` ON ({$f1} = {$f2})";
			}
		}

		$sql = "`{$this->schema}`.`{$this->table}` `t0`\n" . join("\n", $joins);

		return $sql;
	}

	private function getStructure()
	{
		static $structure;

		if (!$structure)
		{
			$sql = "SELECT *
			        FROM `information_schema`.`key_column_usage`
			        WHERE (`TABLE_SCHEMA` = '{$this->params['db']}' AND `TABLE_NAME` = '{$this->table}')
			           OR (`REFERENCED_TABLE_SCHEMA` = '{$this->params['db']}' AND `REFERENCED_TABLE_NAME` = '{$this->table}')";
			$structure['keys']['all'] = $this->query($sql, 'array');

			$types = array(1 => 'main', 2 => 'to', 3 => 'from');

			// Make sure the main table goes first
			$key1 = "{$this->schema}.{$this->table}";
			$tables[$key1] = 1;
			$tmpTables['all'][] = $key1;
			$tmpTables[$types[1]][] = $key1;

			foreach ($structure['keys']['all'] as $key)
			{
				$key2 = "{$key['REFERENCED_TABLE_SCHEMA']}.{$key['REFERENCED_TABLE_NAME']}";
				if (!isset($tables[$key2]) && $key2 != '.')
				{
					$tables[$key2] = 2;
					$tmpTables['all'][] = $key2;
					$tmpTables[$types[2]][] = $key2;
				}

				$key3 = "{$key['TABLE_SCHEMA']}.{$key['TABLE_NAME']}";
				if (!isset($tables[$key3]) && $key3 != '.')
				{
					$tables[$key3] = 3;
					$tmpTables['all'][] = $key3;
					$tmpTables[$types[3]][] = $key3;
				}
			}

			foreach ($tmpTables as $type => $list)
			{
				$structure['tables'][$type] = array_values(array_unique($list));
			}

			// Prepare sql to filter query in search of all relevant columns
			foreach ($structure['tables']['all'] as $key)
			{
				$info = explode('.', $key);
				$filter[] = "(`TABLE_SCHEMA` = '{$info[0]}' AND `TABLE_NAME` = '{$info[1]}')";
			}

			$filterSql = empty($filter) ? 0 : join(' OR ', $filter);

			// Get the structure and candidate columns of each listed table
			$sql = "SELECT *
			        FROM `information_schema`.`columns`
			        WHERE (`TABLE_SCHEMA` = '{$this->schema}' AND `TABLE_NAME` = '{$this->table}')
			        OR {$filterSql}";
			$columns = $this->query($sql, 'array');

			foreach ($columns as $column)
			{
				$schema = $column['TABLE_SCHEMA'];
				$table = $column['TABLE_NAME'];
				$field = $column['COLUMN_NAME'];

				$type = $types[$tables["{$schema}.{$table}"]];

				$structure['global'][$type][$schema][$table][$field] = $column;
				$structure['columns']['all'][] = "{$schema}.{$table}.{$field}";
				$structure['columns'][$type][] = "{$schema}.{$table}.{$field}";
			}

			foreach ($structure['keys']['all'] as &$key)
			{
				$c1 = "{$key['TABLE_SCHEMA']}.{$key['TABLE_NAME']}.{$key['COLUMN_NAME']}";
				($c1 != '..') && ($structure['keys']['byCol'][$c1] =& $key);

				$c2 = "{$key['REFERENCED_TABLE_SCHEMA']}.{$key['REFERENCED_TABLE_NAME']}.{$key['REFERENCED_COLUMN_NAME']}";
				($c2 != '..') && ($structure['keys']['byCol'][$c2] =& $key);
			}
		}

		return $structure;
	}

}