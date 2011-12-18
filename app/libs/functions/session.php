<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	function regSes($key, $val){
		$_SESSION['crm'][$key] = $val;
	}

	function getSes( $key ){
		return isset($_SESSION['crm'][$key]) ? $_SESSION['crm'][$key] : NULL;
	}
	
	function clearSes( $key ){
		regSes($key, NULL);
	}

	function loggedIn(){
		return getSes('user');
	}

	function login($user, $pass){
		if( $info=oSQL()->attemptLogin($user, $pass) ){
			if( $info['blocked'] == '1' ){
				return showStatus('Este usuario se encuentra actualmente bloqueado. '.
					'Por más información consulte a un administrador.');
			};
			if( substr($_SERVER['REMOTE_ADDR'], 0, 3) != '192' ){
				if( $fp=fopen('logs/remoteAccess.txt', 'a') ){
					$log = date('d/m/Y H:i:s')." Usuario {$user} loguea desde {$_SERVER['REMOTE_ADDR']}\n\n";
					fwrite($fp, $log);
					fclose( $fp );
				}
			}
			oSQL()->saveLastAccessDate( $user );
			foreach( $info as $key=> $val ) regSes($key, $val);
			doActionsAtLogin();
			return addScript('setTimeout(function(){location.href = location.href;},20);');
		}
		else return showStatus('Nombre de usuario o contraseña incorrectos.');
	}
	
	function logout($msg='Su sesión fue cerrada correctamente.', $type=1){
		saveLog('loginLogout', 'out');
		$_SESSION['crm'] = array();
		oNav()->clear();
		oPermits()->clear();
		oNav()->queueMsg($msg, $type);
		return addScript("location.href = 'index.php';");
	}