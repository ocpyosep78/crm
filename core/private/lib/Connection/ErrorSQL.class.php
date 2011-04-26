<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	class ErrorSQL{
		
		public $conn;
		public $sql;
		public $number;
		public $desc;
		public $errLog;
		
		public $table;
		public $column;
		
		public function __construct( $conn, $sql ){
		
			$this->conn = $conn;
			$this->sql = preg_replace('/\t/', '', $sql);
			$this->number = mysql_errno( $conn );
			$this->desc = NULL;
			$this->table = NULL;
			$this->column = NULL;
			
			# Dont' waste more time if no error occurred
			if( !$this->number ) return;
			
			$this->desc = mysql_error( $conn );
			$this->buildLog();
			# Table
			preg_match('/UPDATE `(\w+)`|INSERT (IGNORE )?INTO `(\w+)`|DELETE FROM `(\w+)`/i', $sql, $match);
			$this->table = array_pop( $match );
			# Field
			if( $this->number == 1062 ){
				preg_match("/for key '(\w+)'/", $this->desc, $match);
				$sql = "SHOW KEYS FROM `{$this->table}`
						WHERE `Key_name` = '{$match[1]}'";
				$res = mysql_fetch_assoc(mysql_query($sql, $conn));
				$this->column = $res['Column_name'];
			}
			elseif( $this->number == 1452 ){
				preg_match('/FOREIGN KEY \(`(\w+)`\) REFERENCES/', $this->desc, $match);
				$this->column = $match[1];
			}
			
		}
		
		public function buildLog(){
			$this->errLog = "Error {$this->number} ({$this->desc}).<br />";
			$this->errLog .= "<span class='sqlError'>({$this->sql})</span>";
		}
		
		public function raiseError(){
			trigger_error("SQL {$this->errLog}", E_USER_NOTICE);
		}
		
		public function getLog(){
			return $this->errLog;
		}
		
		public function saveLog(){
			$log = "\r\n".date('Y-m-d H:i:s')." [{$this->number}]: {$this->desc})\r\n";
			$log .= "SQL: {$this->sql}\r\n";
			if( $fp=fopen('logs/logSQL.txt', 'a') ){
				fwrite($fp, $log);
				fclose( $fp );
			};
		}
		
		public function __toString(){
			return (string)$this->number;
		}
		
	}
	
?>