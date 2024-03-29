<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	require_once( dirname(__FILE__).'/ErrorSQL.php' );
	require_once( dirname(__FILE__).'/AnswerSQL.php' );


	function getConnectionParams(){
		return array(
			'host'	=> DS_HOST,
			'user'	=> DS_USER,
			'pass'	=> DS_PASS,
			'db'	=> DS_SCHEMA,
		);
	}


	abstract class Connection{

		protected $params;

		private $isDevel;				/* Grants direct access to query/modify for debugging */

		private $conn;					/* Mysql link */

		public $error;					/* ErrorSQL Object of last query (if failed) */

		private $messages;

		public $sql;					/* Last query string */
		public $rows;					/* mysql_affected_rows of last query */
		public $resultSet;				/* Resultset from last query */
		public $formattedRes;			/* Result from last query as passed to user */


/***************
** P R O T E C T E D   M E T H O D S
***************/

		public function __construct($params=NULL)
		{
			$this->isDevel = devMode();

			!is_null($params) || ($params = getConnectionParams());
			$this->params = $params;

			$this->conn = mysql_connect($params['host'], $params['user'], $params['pass'], true)
				or die('Unable to connect to database.');

			mysql_select_db($params['db'], $this->conn)
				or die('Unable to open database.');

			$this->clear();
			$this->clearMessages();

			mysql_set_charset('utf8', $this->conn);
		}

		/**
		 * SELECT queries to the database
		 * It returns different things depending on the $mode
			'array':	...a multidimensional array
			'bool':		...a boolean
			'row':		...a numeric array (first record) or string
			'field':	...a string, first field of first record
			'col':		...an associative array (field 0 => field 1)
			'named':	...a comma separated string
			'list':		...a comma separated string
		 */
/* TEMP : should be protected (used in Snippet temporarily) */
		public function query($sql, $mode=NULL, $atts=NULL)
		{
			$this->clear();

			$res = mysql_query(($this->sql=$sql), $this->conn);

			if ($this->findError())
			{
				return false;
			}

			$this->rows = mysql_affected_rows($this->conn);
			$this->resultSet = $res;

			switch ($mode)                                          /* Return result... */
			{
				case 'array': $this->res2array($res);        break;	/* ...as a multidimensional array */
				case 'bool':  $this->res2bool($res);         break;	/* ...as a boolean */
				case 'row':   $this->res2row($res, $atts);   break;	/* ...as a numeric array (first record) or string */
				case 'field': $this->res2field($res, $atts); break;	/* ...as a string, first field of first record */
				case 'col':   $this->res2col($res, $atts);   break;	/* ...as an associative array (field 0 => field 1) */
				case 'named': $this->res2named($res, $atts); break;	/* ...as array but keys are taken from fields in $atts */
				case 'list':  $this->res2list($res);         break;	/* ...as a comma separated string */
				default: return $res;
			}

			return $this->formattedRes;

		}

		/**
		 * Shortcut to queries of type col
		 */
/* TEMP : should be protected (used in Snippet temporarily) */
		public function asHash( $sql ){
			return $this->query($sql, 'col');
		}

		/**
		 * Shortcut to queries of type named (array if id omitted)
		 */
/* TEMP : should be protected (used in Snippet temporarily) */
		public function asList($sql, $id=NULL){
			return $this->query($sql, $id ? 'named' : 'array', $id);
		}

		/**
		 * Queries that insert, update or delete entries
		 * It returns an AnswerSQL object with error status, affected rows, return msg,
		 * etc. (see auxiliary class AnswerSQL for detailed info)
		 */
		protected function modify($sql)
		{
			$ans = new AnswerSQL($this->messages);

			$this->clear();
			$this->clearMessages();

			$this->sql = $sql;

			$res = mysql_query($this->sql , $this->conn);

			$this->findError();
			$ans->buildAnswer($this->error, mysql_affected_rows($this->conn));

			return $ans;
		}

		/**
		 *
		 */
		public function doselect($table, $fields, $filters=array(), $type='array'){
			if( $fields === '*' || $fields === NULL ) $fieldsSQL = '*';
			else $fieldsSQL = '`'.join('`, `', (array)$fields).'`';
			$sql = "SELECT {$fieldsSQL}
					FROM `{$table}`
					WHERE {$this->array2filter($filters, 'AND', '=')}";
			return $this->query($sql, $type);
		}

		/**
		 * Performs a call to self::modify method, for simple inserts
		 * For more complex inserts (insert ignore, on duplicate key update, etc.)
		 * you should write your own method (see self::array2insSQL method
		 * for more info on how you can dinamically design such queries).
		 */
		public function insert($data, $table){
			return $this->modify( $this->array2insSQL($table, $data) );
		}

		/**
		 *
		 */
		public function multipleInsert($data, $table){
			$ans = $this->successAnswer();	# Initialize
			$this->BEGIN();
			foreach( $data as $row ){
				$ans = $this->insert($row, $table);
				if( $ans->error ) return $this->ROLLBACK( $ans );
			}
			return $this->COMMIT( $ans );
		}

		/**
		 * Performs a call to self::modify method, for simple updates
		 * For more complex updates (depending on current fields, or doing maths)
		 * you should write your own method (see self::array2updSQL method
		 * for more info on how you can dinamically design such queries).
		 *
		 * For security reasons, keys must always be given (it might be a problem if
		 * you intend to updateschema all rows in a table; use your own methods if needed).
		 */
		public function update($data, $table, $arrKeys=array()){
			foreach( (array)$arrKeys as $key ){
				if( !isset($data[$key]) ) continue;
				$keys[] = "`{$key}` = '{$data[$key]}'";
				unset( $data[$key] );
			}
			$cond = isset($keys) ? join(' AND ', $keys) : 'FALSE';
			$sql = "UPDATE `{$table}`
					SET {$this->array2updSQL($data)}
					WHERE {$cond}";

			return $this->modify($sql);
		}

		/**
		 * Performs a call to self::modify method, for simple deletes
		 * For more complex deletes (i.e. depending on current fields, or doing maths)
		 * you should write your own method.
		 *
		 * For security reasons, condition filters must always be given (it might be a
		 * problem if you actually intend to delete all; use your own methods if needed).
		 */
		public function delete($table, $conds=array()){
			$conditions = empty($conds) ? 'FALSE' : $this->array2filter($conds);
			$sql = "DELETE FROM `{$table}` WHERE {$conditions}";

			return $this->modify($sql);
		}


/***************
** C O N V E R T   M E T H O D S
***************/

		public function res2bool($res)
		{
			$this->formattedRes = !!mysql_fetch_array($res);
		}

		public function res2array($res)
		{
			$this->formattedRes = array();

			while ($data=mysql_fetch_assoc($res))
			{
				$this->formattedRes[] = $data;
			}
		}

		public function res2row($res, $field=NULL)
		{
			$row = mysql_fetch_assoc( $res );
			$this->formattedRes = $row
				? ( empty($field) ? $row : $row[$field] )
				: ( empty($field) ? array() : NULL );
		}

		public function res2field($res, $field=NULL)
		{
			$row = mysql_fetch_array( $res );
			$this->formattedRes = !is_null($field)
				? (isset($row[$field]) ? $row[$field] : NULL)
				: (isset($row[0]) ? $row[0] : NULL);
		}

		public function res2col($res, $atts=NULL)
		{
			$this->formattedRes = array();

			# First, let's see if we have any result at all
			$data = mysql_fetch_array($res);

			if (empty($data))
			{
				return;
			}

			# Peek at the first row to see if we have requested keys, then reset pointer
			$availKeys = array_keys($data);
			mysql_data_seek($res, 0);

			# Attempt to set keys as requested, try default behavior on failure
			$fKey = (is_array($atts) && !empty($atts['key'])) ? $atts['key'] : 0;
			$fVal = (is_array($atts) && !empty($atts['val'])) ? $atts['val'] : 1;

			# If keys are not part of the result, try $0 => $1 or even $0 => $0
			if (!in_array($fKey, $availKeys, true))
			{
				$fKey = 0;
			}

			if (!in_array($fVal, $availKeys, true))
			{
				$fVal = min(count($availKeys)/2 - 1, 1);
			}

			# Now we're ready to read data
			while ($data=mysql_fetch_array($res))
			{
				$this->formattedRes[$data[$fKey]] = $data[$fVal];
			}

		}

		public function res2named($res, $key=NULL)
		{
			$this->formattedRes = array();

			# Browse all rows and fix each index with key fields
			while ($data=mysql_fetch_assoc($res))
			{
				if ($key && isset($data[$key]))
				{
					$this->formattedRes[$data[$key]] = $data;
				}
				else
				{
					$this->formattedRes[current($data)] = $data;
				}
			}
		}

		public function res2list($res)
		{
			$this->res2col($res, NULL);
			$this->formattedRes = "'".join("','", $this->formattedRes)."'";
		}


		private function findError($silent=false)
		{
			$this->error = new ErrorSQL($this->conn, $this->sql);
			if( !mysql_error($this->conn) ) return false;
			if( !$silent ) $this->error->raiseError();
			$this->error->saveLog();
			return true;
		}

		private function clear(){

			$this->error = NULL;
			$this->sql = '';
			$this->rows = 0;
			$this->resultSet = NULL;
			$this->formattedRes = array();
		}

		private function clearMessages()
		{
			$this->messages = array(
				'success'			=> 'Su consulta finalizó correctamente.',
				'stdError'			=> 'Ocurrió un error desconocido al procesar su consulta. Se ha guardado un registro del error.',
				'duplicate'			=> 'Ocurrió un error (clave duplicada). Se ha guardado un registro del error.',
				'constraint_parent'	=> 'La base de datos ha bloqueado la modificación de este elemento (FK constraint).<br />'.
					'Otros elementos de la base de datos dependen o derivan de él.',
				'constraint_child'	=> 'La base de datos ha bloqueado la modificación de este elemento (FK constraint).<br />'.
					'Este elemento depende o deriva de otros elementos en la base de datos.',
			);
		}


/***************
** T R A N S A C T I O N S
***************/

		public function BEGIN(){

			return $this->query('BEGIN');
		}

		public function ROLLBACK( $ret=NULL ){
			$this->query('ROLLBACK');
			return $ret;
		}

		public function COMMIT( $ret=NULL ){
			$this->query('COMMIT');
			return $ret;
		}


/***************
** F O R M A T T I N G   M E T H O D S
***************/

/* TEMP : should be protected (used in Snippet temporarily) */
		public function fixFilters(&$filters, $substitutions=array()){

			foreach( $substitutions as $old => $new ){
				$key = preg_replace('/^`|`$/', '', $old);
				(isset($filters[$key])
					|| (isset($filters[$old]) && $key = $old)
					|| (isset($filters["`{$old}`"]) && $key = "`{$old}`")
				) && $filters[$new] = $filters[$key];
				unset( $filters[$key] );
			}

			return $filters;

		}

		protected function array2insSQL($table, $data, $modifiers=array()){
			$insertIgnore = (in_array('insertignore', $modifiers)) ? 'INSERT IGNORE' : 'INSERT';
			$onDuplicateUpdate = '';
			if( in_array('onduplicateupdate', $modifiers) ){
				foreach( $data as $k => $v ) $assignments[] = "`{$k}` = VALUES(`{$k}`)";
				$onDuplicateUpdate = 'ON DUPLICATE KEY UPDATE '.join(',', $assignments);
			}
			foreach( $data as $field => $value ){
				$fields[] = $field;
				$values[] = ($value === 'NULL' || $value === 'null' || $value === NULL)
					? 'NULL'
					: "'".mysql_real_escape_string($value)."'";
			}
			if( empty($fields) || count($fields) != count($values) ){
				trigger_error('Connection error: attempted to run a query without enough parameters');
			}
			return "{$insertIgnore} INTO `{$table}` (`".join("`, `", $fields)."`)
					VALUES (".join(", ", $values).")
					{$onDuplicateUpdate}";
		}

		protected function array2updSQL( $data ){
			foreach( $data as $k => $v ){
				$val = ($v == 'NULL' || $v === NULL) ? 'NULL' : "'".mysql_real_escape_string($v)."'";
				$assign[] = "`{$k}` = {$val}";
			}
			return join(',', $assign);
		}

/* TEMP : should be protected (used in Snippet temporarily) */
		public function array2filter($arr, $joint='AND', $compare='LIKE'){
			# Build template string for different possible operators
			$cmpStr['LIKE'] = "%s LIKE '%%%s%%'";
			foreach( array('=', '<>', '>', '<') as $operator ){
				$cmpStr[$operator] = "%s {$operator} '%s'";
			}
			# See if current $compare type is expected, or set equals (=) as default
			$compareTpl = isset($cmpStr[$compare]) ? $cmpStr[$compare] : $cmpStr['='];
			# Process filters to build the SQL filter string, and return it
			foreach( $arr as $k => $v ){
				if($v === NULL || (is_string($v) && strtolower($v) === 'null')){
					$cond[] = "ISNULL(`{$k}`)";
				}
				elseif( $k[0] == '*' ){
					$cond[] = $v;
				}
				elseif( is_array($v) ){
				# When filter values are arrays, second key's meant to override $compare
					$cmp = isset($v[1])
						? (isset($cmpStr[$v[1]]) ? $cmpStr[$v[1]] : $cmpStr['='])
						: $compareTpl;
					if( isset($v[0]) && $v[0] !== '' ){
						$cond[] = sprintf($cmp, $k, addslashes($v[0]));
					}
				}
				elseif( $v !== '' ){
					$cond[] = sprintf($compareTpl, $k, addslashes($v));
				}
			}
			return isset($cond) ? join(" {$joint} ", $cond) : '1';
		}

		/**
		 * Returns an array with all possible values in an ENUM sql field
		 */
		public function enumDefinition($table, $column){
			$sql = "SHOW COLUMNS
					FROM `{$table}`
					WHERE `Field` = '{$column}'";
			$ret = $this->query($sql, 'field', 'Type');
			return explode("','", preg_replace("/^enum\('|'\)$/", '', $ret));
		}


/***************
** C O N F I G   M E T H O D S
***************/

		public function setTimeZone( $zone=NULL ){
			$sql = "SET time_zone = '{$zone}'";
			return $this->modify( $sql );
		}

		public function setErrMsg( $msg ){
			return $this->messages['stdError'] = $msg;
		}

		public function setOkMsg( $msg ){
			return $this->messages['success'] = $msg;
		}

		public function setDuplMsg( $msg ){
			return $this->messages['duplicate'] = $msg;
		}

		public function useGenericMessages(){
			$this->clearMessages();
		}

		private function successAnswer( $code=1 ){
			$ans = new AnswerSQL( $this->messages );
			$ans->successCode = $code;
			return $ans;
		}


/***************
** D E V E L   M E T H O D S   (for debugging and testing only)
***************/

		public function develQuery( $sql, $mode=NULL, $atts=NULL ){
			if( $this->isDevel ) return $this->query($sql, $mode, $atts);
		}

		public function develModify( $sql ){
			if( $this->isDevel ) return $this->modify( $sql );
		}

	}