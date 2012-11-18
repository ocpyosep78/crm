<?php

function doActionsAtLogin(){
	saveLog('loginLogout', 'in');
	oSQL()->removeOldAlerts(getSes('user'), MAX_ALERTS_PER_USER);
	oSQL()->removeOldLogs( MAX_LOGS_GLOBAL );
}

function devMode(){
	return defined('DEVMODE') && DEVMODE;
}

/* Debugging and devel tools */

function scriptFunctions( $path ){

	$prevFuncs = array_pop( get_defined_functions() );
	if( is_file($path) ) include($path);
	$newFuncs = array_diff(array_pop(get_defined_functions()), $prevFuncs);

	sort($newFuncs);

	return $newFuncs;

}