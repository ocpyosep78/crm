<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/*
	Methods that use Connection's modify() method, return an AnswerSQL object.
	
	This object contains the following public attributes (note to self: outdated list):
		* msg	/> A string personalized message (defaults to '')
		* code	/> Either true or false
		* rows	/> mysql_affected_rows() returned by the query
		
	The personalized msg is set by assigning a string to each property, using:
		* ->setErrMsg( 'error message to print if query fails' );
		* ->setOkMsg( 'success message to print if query succeeds' );
		
*/
	
	require_once( CONNECTION_PATH );


	class Stats extends Connection{
		
		public function count($tbl, $filter){
			$sql = "SELECT COUNT(*)
					FROM `{$tbl}`
					WHERE {$this->array2filter($filter, 'AND', '=')}";
			return $this->query($sql, 'field');
		}
		
		public function countAgendaEvents( $filter=array() ){
			$sql = "SELECT COUNT(*)
					FROM `events` `e`
					LEFT JOIN `events_customers` USING (`id_event`)
					WHERE {$this->array2filter($filter, 'AND', '=')}";
			return $this->query($sql, 'field');
		}
		
		public function getLastEvent( $filter=array() ){
			$sql = "SELECT	`e`.*
					FROM `events` `e`
					LEFT JOIN `events_customers` USING (`id_event`)
					WHERE {$this->array2filter($filter, 'AND', '=')}
					ORDER BY `id_event` DESC
					LIMIT 1";
			return $this->query($sql, 'row');
		}
		
	}