<?php


function page_users()
{
	return oSnippet()->addSnippet('commonList', 'users');
}

function page_usersInfo($acc)
{
	$user = oSQL()->getUser($acc);

	if (empty($user))
	{
		$msg = 'No se encontr� el usuario buscado.';
		return oNav()->redirectTo('users', NULL, $msg);
	}

	$id = $user['user'];
	$self = ($id == getSes('user'));
	$lastAccess = $self ? getSes('last_access') : $user['last_access'];

	# Make sure Tabs sees this page as users/usersInfo, even if it comes from home/home
	oTabs()->setPage( 'usersInfo' );

	# Block 'Personal'
	oFormTable()->clear();
	oFormTable()->addRow('Nombre', "{$user['name']} {$user['lastName']}");
	oFormTable()->addRow('Tel�fono', $user['phone']);
	oFormTable()->addRow('Direcci�n', $user['address']);
	oFormTable()->addRow('Email', $user['email']);
	oFormTable()->addRow($self ? 'Acceso Previo' : '�ltimo Acceso', $lastAccess
		? date('d-m-Y H:i:s', strtotime($lastAccess))
		: "<span id='firstLoginMsg'>".($self ? 'Primer Login' : 'Nunca')."</span>");
	$blocks[] = oFormTable()->getTemplate();

	# Block 'Interno'
	oFormTable()->clear();
	oFormTable()->addRow('Usuario', $user['user']);
	oFormTable()->addRow('Perfil', $user['profile']);
	oFormTable()->addRow('N�mero', $user['employeeNum']);
	oFormTable()->addRow('Departamento', $user['department']);
	oFormTable()->addRow('Cargo', $user['position']);
	$blocks[] = oFormTable()->getTemplate();

	oSmarty()->assign('blocks', $blocks);

	oSmarty()->assign('isSelf', $self);
	oSmarty()->assign('userID', $id);
	oSmarty()->assign('userImg', getUserImg($id));
	oSmarty()->assign('sid', time());

	oSmarty()->assign('userData', oFormTable()->getTemplate());

	if ($self && (oNav()->realPage == 'home'))
	{
		oSmarty()->assign('comboList', NULL);
	}
	else
	{
		oLists()->includeComboList('users', NULL, $user['user']);
	}

	return oTabs()->start();
}

function page_editUsers($acc=NULL)
{
	if ($acc)   // Edit mode
	{
		$user = oSQL()->getUser($acc);
		if (empty($user))
		{
			$msg = 'Usuario no encontrado.';
			return oNav()->loadContent('users', array(), $msg);
		}

		if ($user['id_profile'] < getSes('id_profile'))
		{
			$msg = 'No es posible editar usuarios con perfil m�s alto que el suyo.';
			return oNav()->goBack($msg);
		}
	}

	# See if we're editing self account
	$self = ($acc == getSes('user'));

	# Whether we're editing (or creating a new user)
	$edit = !empty($user);

	oFormTable()->clear();
	oFormTable()->setPrefix( $edit ? 'editUsers_' : 'createUsers_' );

	# Block 'Cuenta'
	if( !$edit || !$self ) oFormTable()->addTitle( 'Cuenta' );

	if( !$edit ){
		oFormTable()->addInput('Usuario', array('id' => 'user'));
		oFormTable()->addInput('Contrase�a', array('id' => 'pass'), 'password');
	}
	else{
		oFormTable()->hiddenRow();
		oFormTable()->addInput('', array('id' => 'user'), 'hidden');
	}

	if( !$self ){
		oFormTable()->addCombo('Perfil',
			array('' => '(seleccionar)') + oLists()->profiles( getSes('id_profile') ),
			array('id' => 'id_profile') );
	}

	# Block 'Personal'
	oFormTable()->addTitle( 'Personal' );
	oFormTable()->addInput('Nombre', array('id' => 'name'));
	oFormTable()->addInput('Apellidos', array('id' => 'lastName'));
	oFormTable()->addInput('Tel�fono', array('id' => 'phone'));
	oFormTable()->addInput('Direcci�n', array('id' => 'address'));
	oFormTable()->addInput('Email', array('id' => 'email'));

	oFormTable()->addTitle( '' );
	oFormTable()->addFile('Imagen', array('id' => 'img'), $edit ? 'editUsers' : 'createUsers');
	oFormTable()->addTitle( '' );

	# Block 'Interno'
	oFormTable()->addTitle( 'Interno' );
	oFormTable()->addInput('N�mero', array('id' => 'employeeNum'));
	oFormTable()->addCombo('Departamento',
		array('' => '(seleccionar)') + oLists()->departments(),
		array('id' => 'id_department'));
	oFormTable()->addInput('Cargo', array('id' => 'position'));

	if ($edit)
	{
		# Block 'Informaci�n'
		oFormTable()->addTitle( 'Informaci�n' );
		oFormTable()->addRow('�ltimo Acceso', $user['last_access']
			? date('d-m-Y H:i:s', strtotime($user['last_access']))
			: "<span style='color:#600000; font-size:12px; font-weight:bold'>Nunca</span>"
		);

		oFormTable()->fillValues($user);
	}

	# Submit line
	oFormTable()->addSubmit($edit ? 'Guardar Cambios' : 'Guardar');

	# Add commands and actions to Xajax response object
	oNav()->updateContent(oFormTable()->getTemplate(), true);

	$prefix = $edit ? 'editUsers' : 'createUsers';
	return addScript("J('#{$prefix}_user').focus();");
}

function page_createUsers()
{
	return page_editUsers();
}