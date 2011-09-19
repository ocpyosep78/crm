<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	return array(
		'editUsers',		# home, users
		'eventInfo',		# agenda
		'closeAgendaEvent',
		'removeAlert',
		'removeAllAlerts',
		'createNotes',
		'deleteNotes',
	);
	
	
	
	function editUsers( $data ){
		
		# Get rid of prefixes in $atts keys (remaining keys match field names in DB)
		oValidate()->preProcessInput($data, 'editUsers_');
		
		if( ($valid=oValidate()->test($data, 'users')) === true ){
			# Set messages to return to the user after processing the request
			oSQL()->setOkMsg("La información fue correctamente actualizada.");
			oSQL()->setErrMsg("Ocurrió un error. Los cambios no fueron guardados.");
			# Request query and catch answer, then return it to the user
			$ans = oSQL()->editUsers( $data );
			$isHome = oNav()->getCurrentModule() == 'home';
			$page = $isHome ? 'home' : 'usersInfo';
			$atts = $isHome ? array() : array($data['user']);
			if( $isHome ) foreach( oSQL()->getUser(getSes('user')) as $k => $v ) regSes($k, $v);
			return oNav()->getPage($page, $atts, $ans->msg, $ans->successCode);
		}
		else return addScript("FTshowTip('editUsers_{$valid['field']}', '{$valid['tip']}');");
		
	}
	
	function eventInfo( $id ){		/* Agenda */
		
		$types = oLists()->agendaEventTypes();
		$users = oLists()->users();
		
		$event = oSQL()->getEventsInfo( $id );
		$event['type'] = $types[$event['type']];
		$event['user'] = $event['target'];
		$event['target'] = $event['user'] ? $users[$event['user']] : '';
		$event['event'] = nl2br( $event['event'] );
		
		oSmarty()->assign('event', $event );
		oSmarty()->assign('editions', oSQL()->getEventEditions($id) );
		oSmarty()->assign('canEditEvent', canEditEvent(getSes('user'), $event['creator'], $event['user']));
		
		addAssign('agenda_eventInfo', 'innerHTML', oSmarty()->fetch('home/eventInfoModal.tpl'));
		
		return addScript("Modal.open('agenda_eventInfo');");
		
	}
	
	function closeAgendaEvent($id, $msg, $rescheduled=false){
		
		$data = array(
			'id_event'		=> $id,
			'user'			=> getSes('user'),
			'comment'		=> $msg,
			'rescheduled'	=> intval($rescheduled),
		);
		$ans = oSQL()->closeAgendaEvent($data);
		
		if( $ans->error ) return showStatus('Ocurrió un error al intentar cerrar el evento.');
		else{
			saveLog('agendaEventClosed', $id, $msg);
			return oNav()->reloadPage('El evento fue cerrado correctamente.', 1);
		}
		
	}

	function removeAlert( $id ){
		
		$ans = oSQL()->removeAlert($id);
		
		return $ans->error ? showStatus('No se pudo eliminar el evento. Inténtelo nuevamente.') : oXajaxResp();
		
	}

	function removeAllAlerts(){
		
		$ans = oSQL()->removeAllAlerts( getSes('user') );
		
		return $ans->error ? showStatus('No se pudo eliminar el evento. Inténtelo nuevamente.') : oXajaxResp();
		
	}
	
	function createNotes($data, $modifier){
	
		# Strip prefix from data fields
		$pfx = 'notes_';
		oValidate()->preProcessInput($data, $pfx);
		
		# A few things depend on where we're using Notes list
		$page = $for = oNav()->getCurrentPage();
		# When on usersInfo page, messages are always for that user only
		if( strstr($page, 'users') ) $data['user'] = $modifier;
		# When on customersInfo page, messages are always about that customer
		elseif( strstr($page, 'customers') ) $data['id_customer'] = $modifier;
		
		# If visibility is used, it'll be either empty (public) or current user's ID (private)
		if( isset($data['visibility']) ) $data['user'] = $data['visibility'];
		
		# Get current note's ID if present (edition) and add who's creating/editting the note
		$id = $data['id_note'] = empty($data['SL_ID']) ? NULL : $data['SL_ID'];
		$data['by'] = getSes('user');
		
		# Unset all info that doesn't go in the database, and info that's useless if empty
		$nullIfEmpty = array('id_note', 'id_customer', 'user');
		foreach( $nullIfEmpty as $item ) if( empty($data[$item]) ) $data[$item] = NULL;
		unset($data['SL_ID'], $data['date'], $data['visibility']);
		
		# Now validate the input and 1. save it on success, or 2. let the user know about it
		if( ($valid=oValidate()->test($data, 'notes')) === true ){
			$ans = oSQL()->{$id ? 'update' : 'insert'}($data, '_notes', 'id_note');
			if( !$ans->error ) return oTabs()->switchTab('notes');
			else return showStatus('No se pudo procesar su consulta. '.
				'Compruebe los datos ingresados y vuelva a intentarlo.');
		}
		else return addScript("FTshowTip('{$pfx}{$valid['field']}', '{$valid['tip']}');");
		
	}
	
	function deleteNotes( $id ){
		
		$ans = oSQL()->delete('_notes', array('id_note' => $id));
		
		if( !$ans->error ) return oTabs()->switchTab('notes');
		else return showStatus('Ocurrió un error. El elemento no pudo ser eliminado.');
		
	}
	
?>