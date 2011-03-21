<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	# This script is meant to be included in another script, not called directly
	if( count(get_included_files()) == 1 ) die();
	
	# App's base directory is where this script is (therefore, make sure to keep it where it belongs)
	define( 'BASE_PATH', str_replace('\\', '/', dirname(__FILE__).'/') );
	
	# Correct path if entry point is not within BASE_PATH
	chdir( BASE_PATH );
	
	# Site constants definitions
	require_once( BASE_PATH.'app/cfg/config.cfg.php' );

	# Developer's local config
	if( is_file($localCfg=BASE_PATH.'app/cfg/local.cfg.php') ) require_once( $localCfg );
	
	# Session
	session_start();
	
	# PHP config
	putenv( "LANG=spanish" );
	setlocale( LC_ALL, 'spanish' );
	date_default_timezone_set( TIME_ZONE );

	# PHP Common Functions (reusable code, app-independant)
	require_once( BASE_PATH.'app/libs/common.php' );
	loadFunctionFiles();

	# Global objects Builder (creates each global object on demand)
	require_once( CORE_PATH.'Builder.class.php' );


?>