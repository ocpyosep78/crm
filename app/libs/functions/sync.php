<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	function sync($user='', $params=array()){
	
		/* Register session timeouts and reload page to force login (or update loggedIn user) */
		if( $user ) checkIfUserStillOnline( $user );
		
		/* Check alerts */
		seekAlerts( $params );
		
		/* Check reminders */
		seekReminders( $params );
		
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
	
	function seekReminders( $params=array() ){
	
		$reminders = oSQL()->seekReminders();
		
		# See which reminders are still active
		$keep = array();
		foreach( $reminders as $reminder ){
			# List reminders
			if( !isset($keep[$reminder['id_reminder']]) ) $keep[$reminder['id_reminder']] = false;
			# Inactive reminders (event already happened) are ignored and removed
			$active = strtotime($reminder['ini']) > time();
			# Add reminder (open event for current user)
			if( $active && $reminder['user'] == getSes('user') ){
				addScript("xajax_eventInfo('{$reminder['id_event']}');");
				$filter = array('id_reminder_user' => $reminder['id_reminder_user']);
				oSQL()->delete('reminders_users', $filter);
			}
			# Do not delete reminders that have other users left to remind
			elseif( $active ) $keep[$reminder['id_reminder']] = true;
		}
		
		# Remove reminders that do not have more users to remind
		foreach( $keep as $id => $keepReminder ){
			if( !$keepReminder ) oSQL()->delete('reminders', array('id_reminder' => $id));
		}
		
		
		
	
	}