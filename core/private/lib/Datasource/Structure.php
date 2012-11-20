<?php


class DS_Structure extends DS_Connect
{

	protected $schema;
	protected $table;


	/**
	 * final protected array getPk()
	 *      Returns the main table's primary key, as an array of properties.
	 *
	 * @return array
	 */
	final public function getPk()
	{
		extract($this->read());

		foreach ($keys['pri'] as $k)
		{
			if (($k['sch1'] == $this->schema && $k['tbl1'] == $this->table))
			{
				return $k['col1'];
			}
		}

		return NULL;
	}

	final protected function read()
	{
		static $structure;

		$id = "{$this->schema}.{$this->table}";

		// First attempt to read it from database cache
		if (!$structure)
		{
			$sql = "SELECT `keys`, `tables`, `columns`
			        FROM `ds_cache_structure`
			        WHERE `id` = '{$id}'
			        AND `stored` > NOW() - 30";
			$structure = @mysql_fetch_assoc($this->query($sql));

			if ($structure)
			{
				foreach ($structure as &$elem)
				{
					$elem = unserialize($elem);
				}
			}
		}

		if (!$structure)
		{
			$keys    = $this->getKeys($this->schema, $this->table);
			$tables  = $this->getTables($keys['all']);
			$columns = $this->getColumns($tables['all'], $keys['all']);

			$structure = compact('keys', 'tables', 'columns');

			$s_keys = addslashes(serialize($keys));
			$s_tables = addslashes(serialize($tables));
			$s_columns = addslashes(serialize($columns));

			$sql = "REPLACE INTO `ds_cache_structure`
			        (`id`, `keys`, `tables`, `columns`)
			        VALUES
					('{$id}', '{$s_keys}', '{$s_tables}', '{$s_columns}')";
			@$this->query($sql);
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
		($res = $this->query($sql)) || ($all = array());

		while ($data=mysql_fetch_assoc($res))
		{
			$all[] = $data;
		}

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

		$ownKey = "`{$this->schema}`.`{$this->table}`";
		$all[$ownKey] = array('schema'  => $this->schema,
		                      'table'   => $this->table,
		                      'type' => array('own' => 'own'));

		$own[$ownKey] =& $all[$ownKey];

		foreach ($keys as $k)
		{
			// Fully qualified name and entry skeleton structure
			$fqn1 = "`{$k['sch1']}`.`{$k['tbl1']}`";
			$fqn2 = "`{$k['sch2']}`.`{$k['tbl2']}`";

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

	private function getColumns($tables, $keys)
	{
		// Prepare sql to filter query in search of all relevant columns
		foreach ($tables as $t)
		{
			$cond[] = "(`TABLE_SCHEMA` = '{$t['schema']}' AND `TABLE_NAME` = '{$t['table']}')";
		}

		$condition = empty($cond) ? 0 : join(' OR ', $cond);

		// Get the structure and candidate columns of each listed table
		$sql = "SELECT `ORDINAL_POSITION` AS 'position',
		               `TABLE_SCHEMA` AS 'schema',
		               `TABLE_NAME` AS 'table',
		               `COLUMN_NAME` AS 'column',
		               `DATA_TYPE` AS 'datatype',
		               `COLUMN_TYPE` AS 'str_datatype',
		               `CHARACTER_SET_NAME` AS 'charset',
		               `COLLATION_NAME` AS 'collation',
		               (`IS_NULLABLE` = 'YES') AS 'null',
		               `COLUMN_DEFAULT` AS 'default',
		               `COLUMN_KEY` AS 'key'/*,
		               `PRIVILEGES` AS 'privileges',
		               `COLUMN_COMMENT` AS 'comment',
		               `TABLE_CATALOG` AS 'table_catalog',
		               `CHARACTER_MAXIMUM_LENGTH` AS 'length',
		               `CHARACTER_OCTET_LENGTH` AS 'octet_length',
		               `NUMERIC_PRECISION` AS 'numeric_precision',
		               `NUMERIC_SCALE` AS 'numeric_scale',
		               `EXTRA` AS 'extra',*/
				FROM `information_schema`.`columns`
				WHERE {$condition}";
		($res = $this->query($sql)) || ($raw = array());

		while ($c=mysql_fetch_assoc($res))
		{
			$tbl_fqn = "`{$c['schema']}`.`{$c['table']}`";
			$col_fqn = "{$tbl_fqn}.`{$c['column']}`";

			$c['null'] = !!$c['null'];

			$c['key']= array('index'   => !!$c['key'],
			                 'unique'  => ($c['key'] == 'UNI'),
			                 'primary' => ($c['key'] == 'PRI'));

			$c['references'] = array();
			$c['referenced'] = array();

			$c['fqn'] = $col_fqn;

			$all[$col_fqn] = $c;

			foreach (array('own', 'src', 'tgt') as $type)
			{
				if (in_array($type, $tables[$tbl_fqn]['type']))
				{
					${$type}[$col_fqn] =& $all[$col_fqn];
				}
			}
		}

		foreach ($keys as $k)
		{
			$fqn1 = "`{$k['sch1']}`.`{$k['tbl1']}`.`{$k['col1']}`";
			$fqn2 = "`{$k['sch2']}`.`{$k['tbl2']}`.`{$k['col2']}`";

			if ($k['sch2'])
			{
				$all[$fqn1]['references'][] =& $all[$fqn2];
				$all[$fqn2]['referenced'][] =& $all[$fqn1];
			}
		}

		foreach ($all as &$c)
		{
			$this->fixDatatype($c);
		}

		$columns = compact('all', 'own', 'src', 'tgt');

		return $columns;
	}

	/**
	 * array columns([array $fields = NULL])
	 *      Given a list of field names, returns an array with the field name as
	 * key and the Column extended info as value. Note that the field name is
	 * preserved as received (i.e. it will nto be extended to match the fully
	 * qualified name of the field).
	 *
	 * @param type $fields
	 * @return type
	 */
	public function columns($fields=NULL)
	{
		extract($this->read());

		if (is_null($fields))
		{
			return $columns['all'];
		}

		$search = array_combine($fields, $fields);

		// Translate each short fieldname to its fully qualified name
		// If there is a conflict, fields from the main table will have priority
		foreach (($columns['own'] + $columns['src']) as $fqn => $c)
		{
			$col = $c['column'];
			$tbl = "`{$c['table']}`.`{$c['column']}`";

			// Find matches with decreasing specificity
			if (($inc = array_search($fqn, $search)) !== false
			 || ($inc = array_search($tbl, $search)) !== false
			 || ($inc = array_search($col, $search)) !== false)
			{
				$search[$inc] = $c;
			}
		}

		// Fields without a match retain the field name instead of an empty list
		foreach ($search as &$v)
		{
			is_array($v) || ($v = array());
		}

		return $search;
	}

	public function tables()
	{
		extract($this->read());
		return $tables['all'];
	}

	public function keys()
	{
		extract($this->read());
		return $keys['all'];
	}




/******************************************************************************/
/********************************* T E M P ************************************/
/******************************************************************************/

	/**
	 * private void fixDatatype(array &$col)
	 *      From the real MySQL datatype, get the general type: integer, string,
	 * boolean, time, date, datetime, list.
	 *
	 * @param array &$col
	 * @return void
	 */
	private function fixDatatype(&$col)
	{
		$regex = '_^([^\( ]*) *\(([^\(]*)\) *(.*)$_';
		preg_match($regex, $col['str_datatype'], $matches);

		if (!$matches)
		{
			$matches = array(NULL, $col['datatype'], 0, '');
		}

		array_shift($matches);
		list($type, $len, $extra) = $matches;

		// Correct len to be an integer, set max possible length where missing
		$len = min((int)$len, $this->getTypeMaxLen($type));

		switch (strtoupper($type))
		{
			case 'CHAR':
			case 'VARCHAR':
			case 'BINARY':
			case 'VARBINARY':
			case 'TINYTEXT':
			case 'TEXT':
			case 'MEDIUMTEXT':
			case 'LONGTEXT':
			case 'TINYBLOB':
			case 'BLOB':
			case 'MEDIUMBLOB':
			case 'LONGBLOB':
				$raw = 'string';
				break;

			case 'ENUM':
			case 'SET':
				$raw = 'list';
				break;

			case 'TINYINT':
				$raw = ($len == 1) ? 'boolean' : 'integer';
				break;

			case 'SMALLINT':
			case 'MEDIUMINT':
			case 'INT':
			case 'BIGINT':
				$raw = 'integer';
				break;

			// For these there is no point in trying to give a "max length"
			case 'FLOAT':
			case 'DOUBLE':
			case 'DOUBLEPRECISION':
			case 'DECIMAL':
			case 'NUMERIC':
				$raw = 'float';
				break;

			// These have a max length as strings, but it's not meaningful
			case 'TIME':
			case 'DATE':
			case 'DATETIME':
			case 'TIMESTAMP':
			case 'YEAR':
				$raw = 'date';
				break;
		}

		$col['datatype'] = compact('type', 'len', 'extra', 'raw');

		$col['datatype']['charset'] = $col['charset'];
		$col['datatype']['collation'] = $col['collation'];

		unset($col['str_datatype'], $col['charset'], $col['collation']);
	}

	private function getTypeMaxLen($datatype)
	{
		switch (strtoupper($datatype))
		{
			case 'CHAR':
			case 'BINARY':     return 255;
			case 'VARCHAR':
			case 'VARBINARY':  return 65535;

			case 'TINYTEXT':
			case 'TINYBLOB':   return 255;
			case 'TEXT':
			case 'BLOB':       return 65535;
			case 'MEDIUMTEXT':
			case 'MEDIUMBLOB': return 16777215;
			case 'LONGTEXT':
			case 'LONGBLOB':   return 4294967295;

			case 'ENUM':
			case 'SET':        return 65535;

			case 'TINYINT':    return 255;
			case 'SMALLINT':   return 65535;
			case 'MEDIUMINT':  return 16777215;
			case 'INT':
			case 'INTEGER':    return 4294967295;
			case 'BIGINT':     return 18446744073709551615;

			// For these there is no point in trying to give a "max length"
			case 'FLOAT':
			case 'DOUBLE':
			case 'DOUBLEPRECISION':
			case 'DECIMAL':
			case 'NUMERIC':

			// These have a max length as strings, but it's not meaningful
			case 'TIME':
			case 'DATE':
			case 'DATETIME':
			case 'TIMESTAMP':
			case 'YEAR':

			default:
				return NULL;
		}
	}

	private function enumDefinition($table, $column){
		$sql = "SHOW COLUMNS
				FROM `{$table}`
				WHERE `Field` = '{$column}'";
		$ret = $this->query($sql, 'field', 'Type');
		return explode("','", preg_replace("/^enum\('|'\)$/", '', $ret));
	}

}