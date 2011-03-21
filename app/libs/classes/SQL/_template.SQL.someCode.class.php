<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
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


	class someClass extends Connection{

/***************
** Q U E R Y   M E T H O D S
****************
** (SELECT)
***************/
		

/***************
** M O D I F Y   M E T H O D S
****************
** (INSERT, UPDATE)
***************/
			

/***************
** M O D I F Y   M E T H O D S
****************
** (DELETE)
***************/
		
	}

?>