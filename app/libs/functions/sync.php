<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	function sync($user='', $params=array()){
	
		/* Register session timeouts and reload page to force login (or update loggedIn user) */
		if( $user ) checkIfUserStillOnline( $user );
		
		/* Check alerts */
		seekAlerts( $params );
		
		return oXajaxResp();
	
	}
	
	function checkIfUserStillOnline( $user ){
		if( $user == getSes('user')  ) return;
		if( oSQL()->getLastLoginLogout($user) == 'in' ) saveLog('loginLogout', 'out', 'timed out', $user);
		oNav()->queueMsg('Su sesión fue cerrada. Debe iniciar sesión nuevamente para continuar.', 'warning');
		return addScript("location.href = 'index.php';");
		
	}
	
	function seekAlerts( $params=array() ){
	
		if( !($user=getSes('user')) ) return;
		
		$logsFrom = empty($params['from']) ? 0 : $params['from'];
		
		oAlerts()->browseLogs( $logsFrom );
		oAlerts()->processLogs();
		
		$alerts = oAlerts()->getAlerts();
		
		addScript('sync.process('.toJson($alerts).');');
	
	}

?>