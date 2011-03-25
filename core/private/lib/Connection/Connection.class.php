<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	require_once( dirname(__FILE__).'/ErrorSQL.class.php' );
	require_once( dirname(__FILE__).'/AnswerSQL.class.php' );
 
 
	function getConnectionParams(){
		return array(
			'host'	=> CRM_HOST,
			'user'	=> CRM_USER,
			'pass'	=> CRM_PASS,
			'db'	=> CRM_DB,
		);
	}
	

	abstract class Connection{
	
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
		
		public function __construct( $params=NULL ){
		
			if( is_null($params) ) $params = getConnectionParams();
		
			$this->conn = mysql_connect($params['host'], $params['user'], $params['pass'], true)
				or die('Unable to connect to database.');
			mysql_select_db($params['db'], $this->conn)
				or die('Unable to open database.');
				
			$this->isDevel = (getSes('id_profile') == 1);
			
			$this->clear();
			$this->clearMessages();
			
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
		protected function query($sql, $mode=NULL, $atts=NULL){
		
			$this->clear();
			
			$res = mysql_query(($this->sql=$sql), $this->conn);
			
			if( $this->findError() ) return false;
			
			$this->rows = mysql_affected_rows( $this->conn );
			$this->resultSet = $res;
			
			switch( $mode ){											/* Return result... */
				case 'array':	$this->res2array( $res );		break;	/* ...as a multidimensional array */
				case 'bool':	$this->res2bool( $res );		break;	/* ...as a boolean */
				case 'row':		$this->res2row($res, $atts);	break;	/* ...as a numeric array (first record) or string */
				case 'field':	$this->res2field($res, $atts);	break;	/* ...as a string, first field of first record */
				case 'col':		$this->res2col( $res );			break;	/* ...as an associative array (field 0 => field 1) */
				case 'named':	$this->res2named($res, $atts);	break;	/* ...as array but keys are taken from fields in $atts */
				case 'list':	$this->res2list( $res );		break;	/* ...as a comma separated string */
				default:		return $res;
			}
			return $this->formattedRes;
			
		}
		
		/**
		 * Shortcut to queries of type col
		 */
		protected function asHash( $sql ){
			return $this->query($sql, 'col');
		}
		
		/**
		 * Shortcut to queries of type named (array if id omitted)
		 */
		protected function asList($sql, $id=NULL){
			return $this->query($sql, $id ? 'named' : 'array', $id);
		}
		
		/**
		 * Queries that insert, update or delete entries
		 * It returns an AnswerSQL object with error status, affected rows, return msg,
		 * etc. (see auxiliary class AnswerSQL for detailed info)
		 */
		protected function modify( $sql ){
		
			$ans = new AnswerSQL( $this->messages );
			
			$this->clear();
			$this->clearMessages();
			
			$this->sql = $sql;
			$res = mysql_query($this->sql , $this->conn);
			
			$this->findError();
			$ans->buildAnswer($this->error, mysql_affected_rows($this->conn));
			
			return $ans;
			
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
		 * you intend to update all rows in a table; use your own methods if needed).
		 */
		public function update($data, $table, $arrKeys=array()){
			if( is_string($arrKeys) ) $arrKeys = array( $arrKeys );
			foreach( $arrKeys as $key ){
				if( !isset($data[$key]) ) continue;
				$keys[] = "`{$key}` = '{$data[$key]}'";
				unset( $data[$key] );
			}
			$cond = isset($keys) ? join(' AND ', $keys) : 'FALSE';
			$sql = "UPDATE `{$table}`
					SET {$this->array2updSQL($data)}
					WHERE {$cond}";
			return $this->modify( $sql );
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
			return $this->modify( $sql );
		}
		

/***************
** P R I V A T E   M E T H O D S
***************/
		
		private function res2bool( $res ){
			$this->formattedRes = !!mysql_fetch_array($res);
		}
		
		private function res2array( $res ){
			$this->formattedRes = array();
			while( $data=mysql_fetch_assoc($res) ) $this->formattedRes[] = $data;
		}
		
		private function res2row( $res, $field ){
			$row = mysql_fetch_assoc( $res );
			$this->formattedRes = $row
				? ( empty($field) ? $row : $row[$field] )
				: ( empty($field) ? array() : NULL );
		}
		
		private function res2field($res, $field=NULL){
			$row = mysql_fetch_array( $res );
			$this->formattedRes = !is_null($field)
				? (isset($row[$field]) ? $row[$field] : NULL)
				: (isset($row[0]) ? $row[0] : NULL);
		}
		
		private function res2col( $res ){
			$this->formattedRes = array();
			while( $data=mysql_fetch_row($res) ){
				if( count($data) > 1 ) $this->formattedRes[$data[0]] = $data[1];
				else $this->formattedRes[] = $data[0];
			}
		}
		
		private function res2named($res, $keys=array()){
			$this->formattedRes = array();
			# Make sure keys are given as array, or fix it to be
			if( !is_array($keys) ) $keys = array($keys);
			$cntKeys = count($keys);	# Amount of keys
			# Browse all rows and fix each index with key fields
			while( $data=mysql_fetch_assoc($res) ){
				$arrIDs = array();
				foreach( $keys as $key ) if( isset($data[$key]) ) $arrIDs[] = $data[$key];
				# Don't use key fields as index if a key field is missing in data
				if( !$cntKeys || empty($arrIDs) || count($arrIDs) != $cntKeys ){
					$this->formattedRes[] = $data;
				}
				else{
					$idRow = join('__|__', $arrIDs);
					$this->formattedRes[$idRow] = $data;
				}
			}
		}
		
		private function res2list( $res ){
			$this->res2col( $res );
			$this->formattedRes = "'".join("','", $this->formattedRes)."'";
		}
		
		
		private function findError( $silent=false ){
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
		
		private function clearMessages(){
		
			$this->messages = array(
				'success'		=> 'Su consulta finalizó correctamente.',
				'stdError'		=> 'Ocurrió un error desconocido al procesar su consulta. Se ha guardado un registro del error.',
				'duplicate'		=> 'Ocurrió un error (clave duplicada). Se ha guardado un registro del error.',
				'constraint'	=> 'La base de datos ha bloqueado la modificación de esta propiedad (FK constraint).',
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
		
		protected function fixFilters($filters, $substitutions=array()){
			foreach( $substitutions as $old => $new ){
				if( isset($filters[$old]) ) $filters[$new] = $filters[$old];
				unset( $filters[$old] );
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
					: "'".mysql_escape_string($value)."'";
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
				$val = ($v == 'NULL' || $v === NULL) ? 'NULL' : "'".mysql_escape_string($v)."'";
				$assign[] = "`{$k}` = {$val}";
			}
			return join(',', $assign);
		}
		
		protected function array2filter($arr, $joint='AND', $compare='LIKE'){
			
			# Build template string for different possible operators
			$cmpStr['LIKE'] = "%s LIKE '%%%s%%'";
			foreach( array('=', '<>', '>', '<') as $operator ){
				$cmpStr[$operator] = "%s {$operator} '%s'";
			}
			# See if current $compare type is expected, or set equals (=) as default
			$compareTpl = isset($cmpStr[$compare]) ? $cmpStr[$compare] : $cmpStr['='];
			# Process filters to build the SQL filter string, and return it
			foreach( $arr as $k => $v ){
				if( $k[0] == '*' ) $cond[] = $v;
				elseif( is_array($v) ){
					$cmp = isset($v[1])
						? (isset($cmpStr[$v[1]]) ? $cmpStr[$v[1]] : $cmpStr['='])
						: $compareTpl;
					if( isset($v[0]) && $v[0] !== '' ) $cond[] = sprintf($cmp, $k, $v[0]);
				}
				elseif( $v !== '' ) $cond[] = sprintf($compareTpl, $k, $v);
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

?>