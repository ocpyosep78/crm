<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	function doActionsAtLogin(){
		saveLog('loginLogout', 'in');
		oSQL()->removeOldAlerts(getSes('user'), MAX_ALERTS_PER_USER);
		oSQL()->removeOldLogs( MAX_LOGS_GLOBAL );
	}
	
	function devMode(){
		return defined('DEVELOPER_MODE') && DEVELOPER_MODE;
	}

/* Debugging and devel tools */

	function scriptFunctions( $path ){
	
		$prevFuncs = array_pop( get_defined_functions() );
		if( is_file($path) ) include($path);
		$newFuncs = array_diff(array_pop(get_defined_functions()), $prevFuncs);
		
		sort($newFuncs);
		
		return $newFuncs;
		
	}

?>