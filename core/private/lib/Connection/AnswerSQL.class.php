<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	define('SQL_ERROR_DUPLICATE', 1062);
	define('SQL_ERROR_CONSTRAINT', 1452);

	class AnswerSQL{
	
		private $messages;
		
		public $error = NULL;
		public $errDesc = '';
		
		public $msg = '';
		public $rows = NULL;
		public $ID = NULL;
		
		public $table;
		public $column;
		
		public $successCode = 0;
		
		public function __construct( $messages=array() ){
			
			$this->messages = $messages;
			
		}
		
		public function buildAnswer( $Error, $rows=NULL){
		
			$field = NULL;
			if( $Error->number == SQL_ERROR_DUPLICATE ){
				$this->messages['stdError'] = $this->messages['duplicate'];
			}
			elseif( $Error->number == SQL_ERROR_CONSTRAINT ){
				$this->messages['stdError'] = $this->messages['constraint'];
			}
		
			$this->msg = $Error->number ? $this->messages['stdError'] : $this->messages['success'];
			$this->error = $Error->number;
			$this->errDesc = $Error->desc;
			$this->successCode = intval(!$Error->number);	/* 1 = ok, 0 = error */
			$this->rows = $rows;			/* Usefull for updates, irrelevant for inserts */
			$this->ID = mysql_insert_id( $Error->conn );
			$this->table = $Error->table;
			$this->column = $this->field = $Error->column;
			
			unset( $this->messages );
			
		}
		
	}

?>