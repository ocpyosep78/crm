<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	function page_home(){
	
		return oNav()->redirectTo('usersInfo', array(getSes('user')));
		
	}

	function page_editAcc(){
		
		$user = oSQL()->getUser( getSes('user') );
		
		oFormTable()->clear();
		oFormTable()->setPrefix( 'editAcc_' );
		oFormTable()->setFrameTitle( 'Cambiar Contraseña' );
		
		# Block 'Cuenta'
		oFormTable()->addTitle( "Cuenta ({$user['user']})" );
		oFormTable()->addInput('Contraseña Actual', array('id' => 'oldPass'), 'password');
		oFormTable()->addInput('Nueva Contraseña', array('id' => 'newPass1'), 'password');
		oFormTable()->addInput('Repetir Contraseña', array('id' => 'newPass2'), 'password');
		
		# Block 'Información'
		oFormTable()->addTitle( 'Información' );
		oFormTable()->addRow('Último Acceso', $user['last_access']
			? date('d-m-Y H:i:s', strtotime($user['last_access']))
			: "<span style='color:#600000; font-size:12px; font-weight:bold'>Nunca</span>"
		);
		
		# Submit line
		oFormTable()->addSubmit( 'Guardar Cambios' );
		
		# Set onsubmit action (submitting through xajax)
		oFormTable()->xajaxSubmit('editAcc');
		
		# Add commands and actions to Xajax response object
		oNav()->updateContent( oFormTable()->getTemplate(), true );
		return addScript("\$('editAcc_oldPass').focus();");
		
	}

	function page_editAccInfo( $user='' ){
		
		require_once(MODS_PATH.'users/pages.php');
		
		return page_editUsers( getSes('user') );
		
		
	}

	function page_createEvent(){
	
		return page_editEvent();
		
	}

	function page_editEvent( $id=NULL ){		/* When $id is not given, it's a new event */
		
		$event = $id ? oSQL()->getEventsInfo($id) : array();
		if( $id && empty($event) ) return oNav()->getPage('agenda', array(), 'Evento no encontrado.');
		
		if( !empty($event) ){		# Fix data to fit fields organization upon editting
			$event['iniDate'] = substr($event['ini'], 0, 10);
			$event['iniTime'] = substr($event['ini'], 11, 5);
			$event['endTime'] = $event['end'] ? substr($event['end'], 11, 5) : '';
			unset($event['ini'], $event['end']);
		}
		
		$users = oLists()->users();
		oSmarty()->assign('users', $users);
		
		# Reminders
		$remindees = array();
		if( $id && $event['id_reminder'] ){
			$filter = array('id_reminder' => $event['id_reminder']);
			$remindees = oSQL()->select('reminders_users', 'user', $filter, 'col');
		}
		else $event['reminder'] = $id ? 0 : 30;
		oSmarty()->assign('reminder', $event['reminder']);
		oSmarty()->assign('remindees', $remindees);
		
		# Block Datos Requeridos
		oFormTable()->clear();
		oFormTable()->setPrefix('evt_');
		oFormTable()->addTitle('Parámetros del Evento');
		oFormTable()->addInput('Fecha', array(
			'id' => 'iniDate',
			'class' => 'input calendar',
			'value' => date('Y-m-d'))
		);
		oFormTable()->addInput('Hora Inicio', array('id' => 'iniTime'));
		oFormTable()->addInput('Hora Fin', array('id' => 'endTime'));
		oFormTable()->addCombo('Tipo',
			oLists()->agendaEventTypes(),
			array('id' => 'type'));
		oFormTable()->addArea('Descripción', array(
			'id'	=> 'event',
			'style'	=> 'height:140px; width:320px;'
		) );
		if( $id ) oFormTable()->fillValues( $event );		# Fill table with values (editting)
		oSmarty()->assign('required', oFormTable()->getTemplate());
		
		# Block Configuración avanzada
		oFormTable()->clear();
		oFormTable()->setPrefix('evt_');
		oFormTable()->addTitle('Parámetros Opcionales');
		oFormTable()->addCombo('Usuario Asignado',
			array('(sin especificar)') + $users,
			array('id' => 'target'));
		oFormTable()->addCombo('Cliente relacionado',
			array('(sin especificar)') + oLists()->customers(),
			array('id' => 'id_customer'));
		
		if( $id ) oFormTable()->fillValues( $event );		# Fill table with values (editting)
		oSmarty()->assign('optional', oFormTable()->getTemplate());
		
		oSmarty()->assign('id_event', $id ? $id : '');
	
		return oNav()->updateContent('home/editEvent.tpl');
	
	}

	function page_agenda($firstDay=NULL, $currFilters=array(), $showRescheduled=1){
		
		/* If $firstDay is not given, start on last Monday */
		if( empty($firstDay) ) $firstDay = 0;
		/* If it's given as a date, or the format is wrong */
		elseif( !is_numeric($firstDay) ){
			if( $dayNum=strtotime($firstDay) ) $firstDay = ceil(($dayNum - time()) / 86400);
			else $firstDay = 0;
		}
		
		while( date('N', strtotime("{$firstDay} days")) != 1 ) $firstDay--;
		
		foreach( $currFilters as $key => $filter ) if( empty($filter) ) unset($currFilters[$key]);
		$range = array(
			'ini'	=> date('Y-m-d', strtotime("{$firstDay} days")),
			'end'	=> date('Y-m-d', strtotime(($firstDay + AGENDA_DAYS_TO_SHOW - 1).' days')),
		);
		$events = oSQL()->getEventsInfo(NULL, $range, $currFilters);
		
		# Get data and pre-process it
		$data = array();
		for( $i=$firstDay ; $i < ($firstDay + AGENDA_DAYS_TO_SHOW) ; $i++ ){
			$date = date('Y-m-d', strtotime("{$i} days"));
			$data[$date] = array(
				'date'		=> $date,
				'isToday'	=> !$i,
				'events'	=> array(),
			);
		}
		
		# Fill days with events
		foreach( $events as $event ){
			$event['event'] = nl2br( $event['event'] );	/* Textarea linefeeds to <br> */
			$data[substr($event['ini'], 0, 10)]['events'][] = $event;
		}
		foreach( $data as $day ) $days[] = $day;
		
		# Filters
		$filters = array();
		$filters['type'] = array(
			'name'		=> 'Tipo',
			'options'	=> array(''=>'(todos)') + oLists()->agendaEventTypes(),
		);
		$filters['user'] = array(
			'name'		=> 'Usuario',
			'options'	=> array(''=>'(todos)') + oLists()->users(),
		);
		
		# Smarty assignments
		oSmarty()->assign('data', isset($days) ? $days : array());
		oSmarty()->assign('currFilters', $currFilters + array_fill_keys(array_keys($filters), ''));
		oSmarty()->assign('prev', $firstDay - 7);
		oSmarty()->assign('next', $firstDay + 7);
		oSmarty()->assign('types', oLists()->agendaEventTypes());
		oSmarty()->assign('filters', $filters);
		oSmarty()->assign('showRescheduled', $showRescheduled);
		
		# Hide menu for widescreen presentation
		hideMenu();
	
	}

	function page_agendaDay( $date=NULL ){
		
		if( !$date ) return oNav()->abortFrame('Faltan datos requeridos para cargar la página.');
	
		# Basic structure of data to be passed
		$day['date'] = $date;
		$day['isToday'] = true;
		$day['events'] = oSQL()->getEventsInfo(NULL, $date);
	
		# Fill day with events
		foreach( $day['events'] as &$evt ) $evt['event'] = nl2br( $evt['event'] );
		oSmarty()->assign('day', $day);
		oSmarty()->assign('types', oLists()->agendaEventTypes());
		
	}
	
	function page_calls(){
	
		return oSnippet()->addSnippet('commonList', 'calls');
	
	}
	
	function page_callsInfo( $id ){
	
		return oSnippet()->addSnippet('viewItem', 'calls', array('filters' => $id));
	
	}
	
	function page_createCalls(){
	
		return oSnippet()->addSnippet('createItem', 'calls');
		
	}
	
	function page_editCalls( $id ){
	
		return oSnippet()->addSnippet('editItem', 'calls', array('filters' => $id));
		
	}
	
	function page_activity_technical(){
	
		getActivity( 'technical' );
		
	}
	
	function page_activity_sales(){
	
		getActivity( 'sales' );
		
	}
	
	function page_logs(){
	
		$data = array(
			'Acceso Remoto'				=> openLogs('remoteAccess'),
			'Errores en Consultas SQL'	=> openLogs('logSQL'),
			'Errores de Logueo'			=> openLogs('loggingErrors'),
		);
		oSmarty()->assign('data', $data);
		
	}
	
?>