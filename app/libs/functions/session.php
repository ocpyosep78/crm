<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

function regSes($key, $val)
{
	$_SESSION['crm'][$key] = $val;
}

function getSes($key)
{
	return isset($_SESSION['crm'][$key]) ? $_SESSION['crm'][$key] : NULL;
}

function clearSes($key)
{
	regSes($key, NULL);
}

function loggedIn()
{
	if (false && !getSes('user') && !empty($_COOKIE['crm_user']))
	{
		$user = substr($_COOKIE['crm_user'], 0, -40);
		$cookie = substr($_COOKIE['crm_user'], -40);

		$info = oSQL()->getUser($user);

		if ($info && ($info['cookie'] == $cookie))
		{
			acceptLogin($info);
		}
	}

	return getSes('user');
}

function login($user, $pass)
{
	$info = oSQL()->getUser($user);

	if ($info && ($info['pass'] == md5($pass)))
	{
		if ($info['blocked'] == '1')
		{
			return say('Este usuario se encuentra actualmente bloqueado. '.
				'Por más información consulte a un administrador.');
		}

		acceptLogin($info);

		return addScript('setTimeout(function(){location.href = location.href;},20);');
	}
	else
	{
		return say('Nombre de usuario o contraseña incorrectos.');
	}
}

function acceptLogin($info)
{
	$ip = $_SERVER['REMOTE_ADDR'];

	if (in_array(substr($ip, 0, 3), array('192', '127')))
	{
		$cookie = sha1(time() . rand(1, time()));
		$expire = time() + (3600*24*30);
		setcookie('crm_user', "{$info['user']}{$cookie}", $expire);
	}
	elseif ($fp=fopen('logs/remoteAccess.txt', 'a'))
	{
		$date = date('d/m/Y H:i:s');
		$log = "{$date}: Usuario {$info['user']} loguea desde {$ip}\n\n";
		fwrite($fp, $log);
		fclose($fp);
	}

	oSQL()->saveLastAccess($info['user'], isset($cookie) ? $cookie : NULL);

	foreach ($info as $key => $val)
	{
		regSes($key, $val);
	}

	doActionsAtLogin();
}

function logout($msg='Su sesión fue cerrada correctamente.', $type=1)
{
	saveLog('loginLogout', 'out');

	setcookie('crm_user', '');
	$_SESSION['crm'] = array();

	oNav()->clear();
	oPermits()->clear();
	oNav()->queueMsg($msg, $type);

	return addScript("location.href = 'index.php';");
}