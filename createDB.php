<?php

	require_once(dirname(__FILE__).'/initialize.php');

	if( !DEVELOPER_MODE ) die();
	
	
	/* Create database and connect */
	
	$conn = mysql_connect(CRM_HOST, CRM_USER, CRM_PASS, true);
	
	$create = "	CREATE DATABASE `".CRM_DB."`
				DEFAULT CHARACTER SET latin1 COLLATE latin1_spanish_ci;";
				
	mysql_query($create, $conn)
		or die( mysql_error($conn) );
		
	mysql_select_db(CRM_DB, $conn)
		or die( mysql_error($conn) );
	
	
	/* Read and parse queries */
	
	$stream = '';
	$h = fopen('sql/crm_minimal.sql', 'r');
	while( $data=fread($h, 1024) ) $stream .= $data;
	fclose( $h );
	
	
	/* Create tables */
	
	$queries = explode(';', $stream);
	foreach( $queries as $sql ){
		if( !trim($sql) ) continue;
		if( !mysql_query($sql, $conn) ){
			$error = mysql_error( $conn );
			mysql_query( 'ROLLBACK' );
			die( $error );
		}
	}
		
		
	
	header( 'location:index.php' );

?>