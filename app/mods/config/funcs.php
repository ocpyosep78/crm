<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	function oConfig(){
		if( !isset($GLOBALS['Config']) ){
			require_once( dirname(__FILE__).'/lib/Config.class.php' );
			$GLOBALS['Config'] = new MOD_Config();
		}
		return $GLOBALS['Config'];
	}
	
	function permitsFilters(){
		return array(
			'%'			=> 'Todos',
			'module'	=> 'Módulos',
			'page'		=> 'Páginas',
			'permit'	=> 'Otros',
		);
	}
	
	
	/**
	 * Define content's Smarty vars for main config tab.
	 */
	function subCfgMain(){
	
	}
	function subCfgProfiles(){
		
		oSmarty()->assign('profiles', oLists()->profiles(2));
		oSmarty()->assign('permits', array());
		
		oSmarty()->assign('permitsFilter', '%');
		oSmarty()->assign('permitsFilters', permitsFilters());
	
	}
	function subCfgAlerts(){
	
	}
	function subCfgdevPermits(){
	
	}
	function subCfgdevMods(){
	
	}
	function subCfgdevPages(){
	
	}
	function subCfgdevInsert(){
	
	}
	function subCfgdevQueries(){
	
	}