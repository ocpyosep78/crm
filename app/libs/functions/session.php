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

	function loggedIn()
	{
		// Keep session alive by cookies
		if (!getSes('user') && !empty($_COOKIE['crm_user']))
		{
			$user = substr($_COOKIE['crm_user'], 0, -40);
			$cookie = substr($_COOKIE['crm_user'], -40);

			$info = oSQL()->getUser($user);

			// Clear saved cookie by saving last access without passing a cookie
			if ($info && $info['blocked'])
			{
				oSQL()->saveLastAccess($info['user']);
			}
			elseif ($info && ($info['cookie'] == $cookie))
			{
				$cookie = sha1(time() . rand(1, time()));
				$expire = time() + (3600*24*30);
				setcookie('crm_user', "{$info['user']}{$cookie}", $expire);

				oSQL()->saveLastAccess($info['user'], $cookie);

				foreach ($info as $key => $val)
				{
					regSes($key, $val);
				}

				header('Refresh:0');
			}
		}

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
			oSQL()->saveLastAccess( $user );
			foreach( $info as $key=> $val ) regSes($key, $val);
			doActionsAtLogin();
			return addScript('setTimeout(function(){location.href = location.href;},20);');
		}
		else return showStatus('Nombre de usuario o contraseña incorrectos.');
	}

	function logout($msg='Su sesión fue cerrada correctamente.', $type=1){
		saveLog('loginLogout', 'out');

		setcookie('crm_user', '');
		$_SESSION['crm'] = array();

		oNav()->clear();
		oNav()->queueMsg($msg, $type);

		oPermits()->clear();

		return addScript("location.href = '';");
	}