<?php

class BaseAjax
{
	public static function login()
	{
		list($user, $pass) = func_get_args();

		$info = oSQL()->getUser($user);

		if ($info && ($info['pass'] == md5($pass)))
		{
			if ($info['blocked'] == '1')
			{
				return say('Este usuario se encuentra actualmente bloqueado. '.
					'Por más información consulte a un administrador.');
			}

			acceptLogin($info);
			saveLog('loginLogout', 'in');

			return addScript('setTimeout(function(){location.href = location.href;}, 20);');
		}
		else
		{
			return say('Nombre de usuario o contraseña incorrectos.');
		}
	}

	public static function logout()
	{
		saveLog('loginLogout', 'out');

		setcookie('crm_user', '');
		$_SESSION['crm'] = array();

		oNav()->clear();
		oNav()->queueMsg($msg, $type);

		return Response::reload('home');
	}

	public static function test()
	{
		return say("yeah, I was called");
	}

	public function __call($method, $params)
	{
		return say("{$method} is not a valid Ajax ID");
	}

}