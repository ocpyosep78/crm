<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	function page_users(){
	
		return oLists()->printList();
		
	}

	function page_usersInfo( $acc ){
		
		$user = oSQL()->getUser( $acc );
		if( empty($user) ) return oNav()->redirectTo('users', NULL, 'No se encontró el usuario buscado.');
		
		$id = $user['user'];
		$self = $id == getSes('user');
		$lastAccess = $self ? getSes('last_access') : $user['last_access'];
		
		# Make sure Tabs sees this page as users/usersInfo, even if it comes from home/home
		oTabs()->setPage( 'usersInfo' );
		
		# Block 'Personal'
		oFormTable()->clear();
		oFormTable()->addRow('Nombre', "{$user['name']} {$user['lastName']}");
		oFormTable()->addRow('Teléfono', $user['phone']);
		oFormTable()->addRow('Dirección', $user['address']);
		oFormTable()->addRow('Email', $user['email']);
		oFormTable()->addRow($self ? 'Acceso Previo' : 'Último Acceso', $lastAccess
			? date('d-m-Y H:i:s', strtotime($lastAccess))
			: "<span id='firstLoginMsg'>".($self ? 'Primer Login' : 'Nunca')."</span>");
		$blocks[] = oFormTable()->getTemplate();
		
		# Block 'Interno'
		oFormTable()->clear();
		oFormTable()->addRow('Usuario', $user['user']);
		oFormTable()->addRow('Perfil', $user['profile']);
		oFormTable()->addRow('Número', $user['employeeNum']);
		oFormTable()->addRow('Departamento', $user['department']);
		oFormTable()->addRow('Cargo', $user['position']);
		$blocks[] = oFormTable()->getTemplate();
		
		oSmarty()->assign('blocks', $blocks);
		
		oSmarty()->assign('isSelf', $self);
		oSmarty()->assign('userID', $id);
		oSmarty()->assign('userImg', oSQL()->getUserImg($id));
		oSmarty()->assign('sid', time());
		
		oSmarty()->assign('userData', oFormTable()->getTemplate());
		
		if( $self && oNav()->realPage == 'home' ) oSmarty()->assign('comboList', NULL);
		else oLists()->includeComboList('users', NULL, $user['user']);
	
		return oTabs()->start();
		
	}

	function page_editUsers( $acc=NULL ){
		
		if( $acc ){	// Edit mode
			$user = oSQL()->getUser( $acc );
			if( empty($user) ) return oNav()->loadContent('users', array(), 'Usuario no encontrado.');
			if( $user['id_profile'] < getSes('id_profile') ){
				return oNav()->goBack('No es posible editar usuarios con perfil más alto que el suyo.');
			}
		}
		
		# See if we're editting self account
		$self = ($acc == getSes('user'));
		# And whether we're editing (or creating a new user)
		$edit = !empty($user);
		
		oFormTable()->clear();
		oFormTable()->setPrefix( $edit ? 'editUsers_' : 'createUsers_' );
		
		# Block 'Cuenta'
		if( !$edit || !$self ) oFormTable()->addTitle( 'Cuenta' );
		
		if( !$edit ){
			oFormTable()->addInput('Usuario', array('id' => 'user'));
			oFormTable()->addInput('Contraseña', array('id' => 'pass'), 'password');
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
		oFormTable()->addInput('Teléfono', array('id' => 'phone'));
		oFormTable()->addInput('Dirección', array('id' => 'address'));
		oFormTable()->addInput('Email', array('id' => 'email'));

        oFormTable()->addTitle( '' );
        oFormTable()->addFile('Imagen', array('id' => 'img'), $edit ? 'editUsers' : 'createUsers');
        oFormTable()->addTitle( '' );
		
		# Block 'Interno'
		oFormTable()->addTitle( 'Interno' );
		oFormTable()->addInput('Número', array('id' => 'employeeNum'));
		oFormTable()->addCombo('Departamento',
			array('' => '(seleccionar)') + oLists()->departments(),
			array('id' => 'id_department'));
		oFormTable()->addInput('Cargo', array('id' => 'position'));
		
		if( $edit ){
			
			# Block 'Información'
			oFormTable()->addTitle( 'Información' );
			oFormTable()->addRow('Último Acceso', $user['last_access']
				? date('d-m-Y H:i:s', strtotime($user['last_access']))
				: "<span style='color:#600000; font-size:12px; font-weight:bold'>Nunca</span>"
			);
		
			oFormTable()->fillValues( $user );
		}
		
		# Submit line
		oFormTable()->addSubmit( $edit ? 'Guardar Cambios' : 'Guardar' );
		
		# Add commands and actions to Xajax response object
		oNav()->updateContent(oFormTable()->getTemplate(), true);
		
		return addScript("\$('".($edit ? 'editUsers' : 'createUsers')."_user').focus();");
		
	}

	function page_createUsers(){
		
		return page_editUsers();		/* Just editting a non-existant user, aren't we */
		
	}