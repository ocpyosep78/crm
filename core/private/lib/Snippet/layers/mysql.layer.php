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

		$sql = "SELECT {$fieldsSql}\nFROM {$tablesSql}";

		return $sql;
	}

	private function getFieldsSql()
	{
		extract($this->getStructure());

		$fields = array_merge($columns['own'], $columns['src']);

		foreach ($fields as $fqn => $c)
		{
			$col = $c['COLUMN_NAME'];
			$ord = $tables['ordinals']["{$c['TABLE_SCHEMA']}.{$c['TABLE_NAME']}"];

			$fieldsSql[] = "`t{$ord}`.`{$c['COLUMN_NAME']}` AS '{$fqn}'";
		}

		return empty($fieldsSql) ? array('*') : join(",\n       ", $fieldsSql);
	}

	private function getTablesSql()
	{
		extract($this->getStructure());

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

	private function getStructure()
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