<?php


abstract class DS_Connect
{

	private $Error;

	protected $Answer;

	public static $messages = array(
		'success'   => 'Su consulta finalizó correctamente.',
		'error'     => 'Ocurrió un error desconocido al procesar su consulta. Se ha guardado un registro del error.',
		'duplicate' => 'Ocurrió un error (clave duplicada). Se ha guardado un registro del error.',
		'fk_parent' => 'La base de datos ha bloqueado la modificación de este elemento (FK constraint).<br />Otros elementos de la base de datos dependen o derivan de él.',
		'fk_child'  => 'La base de datos ha bloqueado la modificación de este elemento (FK constraint).<br />Este elemento depende o deriva de otros elementos en la base de datos.',
	);


	public function get_last_error()
	{
		return $this->Error;
	}


	/**
	 * protected mixed query(string $sql[, $complex = false])
	 *      Executes a query and returns either a MySQL resource (if $complex is
	 * false) or a DS_Answer object (if $complex is true).
	 *
	 * @param string $sql
	 * @param boolean $complex      If true, return a DS_Answer
	 * @return mixed                Resource or DS_Answer
	 */
	protected function query($sql)
	{
		$res = mysql_query($sql, $this->connect());

		// Save last error's object, for later retrieval
		$this->Error = $this->error($sql);

		// Find out if this was a select-like query or not
		$selects = preg_match('_^(SELECT|SHOW|DESCRIBE)\b_i', $sql);

		$this->Answer = $this->answer($sql, $res);

		return $selects ? $res : $this->Answer;
	}

	private function connect()
	{
		static $conn;

		if (is_null($conn))
		{
			$conn = @mysql_connect(DS_HOST, DS_USER, DS_PASS, true)
				or die('Unable to connect to database.');

			@mysql_select_db(DS_SCHEMA, $conn)
				or die('Unable to open database: ' . (DS_SCHEMA ? DS_SCHEMA : '(empty)'));
		}

		return $conn;
	}

	private function error($sql)
	{
		$Error = new stdClass;

		$Error->sql = $sql;
		$Error->table = $this->table;

		$Error->errno = mysql_errno();
		$Error->error = mysql_error();

		// Get failing field for constraint errors
		switch ($Error->errno)
		{
			case 1062:  // Duplicate
				$regex = "_for key '(\w+)'_";
				@preg_match($regex, $Error->error, $match);
				$sql = "SHOW KEYS FROM `{$Error->table}` WHERE `Key_name` = '{$match[1]}'";
				$res = @mysql_fetch_assoc(@mysql_query($sql));
				$Error->column = $res ? $res['Column_name'] : 'Unknown';
				break;

			case 1452:
				$regex = "_FOREIGN KEY \(`(\w+)`\) REFERENCES_";
				@preg_match($regex, $Error->error, $match);
				$Error->column = $match ? $match[1] : 'Unknown';
				break;

			default:
				$Error->column = 'Unknown';
		}

		$log = "\r\n" . date('Y-m-d H:i:s') . " [{$Error->errno}]: {$Error->error})\r\n" .
		       "SQL: {$Error->sql}\r\n";

		if (defined('DATASOURCE_ERROR_LOG') && DATASOURCE_ERROR_LOG)
		{
			if ($fp=@fopen(DATASOURCE_ERROR_LOG, 'a'))
			{
				fwrite($fp, $log);
				fclose($fp);
			}
		}

		return $Error;
	}

	private function answer($sql, $res)
	{
		$Answer = new stdClass;

		$Answer->sql = $sql;
		$Answer->res = $res;

		$Answer->rows = mysql_affected_rows();
		$Answer->id = mysql_insert_id();

		$Answer->Error = $this->Error;
		$Answer->failed = !!$this->Error->errno;   // Shortcut

		switch ($this->Error->errno)
		{
			case 0:
				$Answer->msg = self::$messages['success'];
				break;

			case 1062:
				$Answer->msg = self::$messages['duplicate'];
				break;

			case 1451:
				$Answer->msg = self::$messages['fk_parent'];
				break;

			case 1452:
				$Answer->msg = self::$messages['fk_child'];
				break;

			default:
				$Answer->msg = self::$messages['error'];
				break;
		}

		return $Answer;
	}

}