<?php


class DS_Result
{

	private $__search;          // Query parameters (filters, limit, order, etc.)
	private $__query;           // The sql query
	private $__datatype;        // Result format (array, named, row, col, res, ...)

	private $__orig_dataset;    // Original result set (unformatted)
	private $__dataset;         // Result (migth be formatted)
	private $__namespace;       // Full namespace (2), partial (1) or none (0)

	private $caller;            // Sql Layer who initialized this object
	private $Answer;


	public function __construct($Answer, $search, $caller)
	{
		$this->caller = $caller;
		$this->Answer = $Answer;

		$this->__search = $search;
		$this->__query = $Answer->sql;
		$this->__orig_dataset = $Answer->res;

		$this->__dataset = $Answer->res;
		$this->__datatype = 'res';
		$this->__namespace = 2;

		// Results are flat by default (will be converted to 'array' as well)
		$this->flat();
	}

	public function get()
	{
		return $this->__dataset;
	}

	/**
	 * DS_Result flat()
	 *      Remove fields namespace dataset. This might mean that some fields
	 * will be overwritten, if called the same but in different tables.
	 *
	 * @return DS_Result
	 */
	public function flat()
	{
		return $this->ns(-1);
	}

	/**
	 * DS_Result ns([boolean $full = false])
	 *      Add/Remove field namespace from dataset
	 *
	 * @param boolean $full
	 * @return DS_Result
	 */
	public function ns($ns=1)
	{
		list($oldNs, $this->__namespace) = [$this->__namespace, (int)$ns];

		$nsRemove = $oldNs - $ns;

		// If we're extending the namespace, we need to restore it full first
		if ($nsRemove < 0)
		{
			$this->convert($this->__datatype);
			$nsRemove = 2 - $ns;
		}
		// Same namespace level, nothing to do
		elseif ($nsRemove === 0)
		{
			return $this;
		}

		switch ($this->datatype)
		{
			case 'res':
				$this->convert('named');

			case 'array':
			case 'named':
				$rows = [];

				foreach ($this->__dataset as $k => $row)
				{
					foreach ($row as $field => $val)
					{
						$fqn_bits = explode('`.`', $field, $nsRemove+1);
						$key = trim(end($fqn_bits), '`');
						$rows[$k][($ns == -1) ? $key : "`{$key}`"] = $val;
					}
				}

				$this->__dataset = $rows;
				break;

			case 'row':
				$row = [];

				foreach ($this->__dataset as $field => $val)
				{
					$key = trim(end(explode('`.`', $field, $nsRemove+1)), '`');
					$row[($ns == -1) ? $key : "`{$key}`"] = $val;
				}

				$this->__dataset = $row;
				break;

			// Other types are originally flat
			default:
			case 'col':
				break;
		}

		return $this;
	}

	/**
	 * DS_Result convert(string $to[, mixed $atts])
	 *      Manipulate the resultset to return it formatted accoring to the $to
	 * code: res, array, named, col, row, field, bool, list.
	 *
	 * @param string $to
	 * @param mixed $atts
	 * @return DS_Result
	 */
	public function convert($to, $atts=NULL)
	{
		$this->__datatype = $to;

		if (mysql_num_rows($this->__orig_dataset))
		{
			mysql_data_seek($this->__orig_dataset, 0);
		}

		$orig =& $this->__orig_dataset;

		switch ($to)
		{
			case 'res':
				$dataset = $orig;
				break;

			case 'array':
			case 'named':
			case 'col':
				$set = $dataset = [];

				while ($data=mysql_fetch_assoc($orig))
				{
					$set[] = $data;
				}

				$unnamed = (($to === 'array') && !$atts);

				if ($set && !$unnamed)
				{
					$key = $this->datasetIdentifyingKey($set[0], $atts);

					// For 'col' we need to get the key for main value as well
					do
					{
						$val = key($set[0]);
					}
					while (($val === $key) && (next($set[0]) !== false));
				}
				foreach ($set as $row)
				{
					$k = empty($key) ? key($row) : $key;
					$v = ($to === 'col') ? $row[$val] : $row;

					$unnamed ? ($dataset[] = $v) : ($dataset[$row[$k]] = $v);
				}

				break;

			case 'row':
				($dataset = mysql_fetch_assoc($orig)) || ($dataset = []);
				break;

			case 'field':
				($row = mysql_fetch_array($orig)) || ($row = []);
				$key = ($atts && is_string($atts)) ? $atts : 0;
				$dataset = isset($row[$key]) ? $row[$key] : NULL;
				break;

			case 'bool':
				$dataset = !!mysql_fetch_array($orig);
				break;

			case 'list':
				while ($data=mysql_fetch_row($orig))
				{
					$set[] = $data[0];
				}
				$dataset = isset($set) ? join(",", $set) : '';
				break;
		}

		$this->__dataset =& $dataset;

		// Restore namespace status
		if ($to !== 'res')
		{
			list($this->__namespace, $ns) = [2, $this->__namespace];
			$this->ns($ns);
		}

		return $this;
	}

	/**
	 * private mixed datasetIdentifyingKey(array $row[, mixed $key = NULL])
	 *      Given a data row and an optional pre-set $key, find out which key of
	 * the set should be used as identifier. This will be the PK when $key is
	 * empty, or $key if it's given and valid (will raise an exception if it is
	 * given but invalid. Returns first key if neither is found.
	 *
	 * @param array $row
	 * @param mixed $key
	 * @return mixed
	 */
	private function datasetIdentifyingKey($row, $given=NULL)
	{
		$key = key($row);

		if ($given)
		{
			if (is_string($given) && isset($row[$given]))
			{
				$key = $given;
			}
			else
			{
				$msg = 'Invalid key given for convert() (' .
					   var_export($given, true) . ')';
				throw new Exception($msg);
			}
		}
		else
		{
			$sch = $this->caller->schema;
			$tbl = $this->caller->table;
			$pk = "`{$sch}`.`{$tbl}`.`{$this->caller->getPk()}`";

			$columns = $this->search->select;

			foreach ($columns as $c)
			{
				list($pos, $col) = [key($c), current($c)];

				if (isset($col['fqn']) && ($col['fqn'] == $pk))
				{
					$key = array_keys($row)[$pos];
				}
			}
		}

		return $key;
	}


/******************************************************************************/
/************************* M A G I C   M E T H O D S **************************/
/******************************************************************************/

	/**
	 * mixed __get()
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