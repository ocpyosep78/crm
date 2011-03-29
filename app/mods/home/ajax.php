<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	return array(
		'editAcc',
		'createEvent',
	);
	
	function editAcc( $atts ){
		
		# Get rid of prefixes in $atts keys (remaining keys match field names in DB)
		oValidate()->preProcessInput($atts, 'editAcc_');
		$valid = oValidate()->test(array('pass' => $atts['newPass1']), 'users');
		
		# If an error occurred, set $err = array(FieldWithError, ErrorTip)
		if( !$atts['oldPass'] ){
			$err = array('oldPass', 'Debe escribir la contraseña actual.');
		}
		elseif( md5($atts['oldPass']) != getSes('pass') ){
			$err = array('oldPass', 'La contraseña actual no es correcta.');
		}
		elseif( !$atts['newPass1'] ){
			$err = array('newPass1', 'Debe escribir su nueva contraseña 2 veces.');
		}
		elseif( !$atts['newPass2'] ){
			$err = array('newPass2', 'Debe escribir su nueva contraseña 2 veces.');
		}
		elseif( $atts['newPass1'] != $atts['newPass2'] ){
			$err = array('newPass2', 'Las contraseñas nuevas no coinciden.');
		}
		elseif( $valid !== true ){
			$err = array('newPass1', $valid['tip']);
		}
		
		if( isset($err) ) return addScript("FTshowTip('editAcc_{$err[0]}', '{$err[1]}');");
		
		# Set message to return to the user after processing the request, if error
		oSQL()->setErrMsg("Ocurrió un error. Su contraseña no fue modificada.");
		# Request query and catch answer, then return it to the user
		$data = array('user' => getSes('user'), 'pass' => md5($atts['newPass1']));
		
		$ans = oSQL()->editUsers( $data );
		if( $ans->error ){
			return showStatus($ans->msg, $ans->successCode);
		}
		elseif( !$ans->rows ){
			$msg1 = 'No se han guardado cambios. Verifique los datos ingresados.';
			$msg2 = 'Es posible que su cuenta no disponga de permisos suficientes.';
			return showStatus("{$msg1}<br />{$msg2}");
		}
		else{
			return logout('Inicie sesión con su nueva contraseña.', 2);
		}
		
	}
	
	function createEvent($data, $id=NULL){
	
		if( $id && !oPermits()->can('editEvent') ) return oPermits()->noAccessMsg();
		
		oValidate()->preProcessInput($data, 'evt_');
		
		# Check that the user has required permissions
		if( $id ){
			$event = oSQL()->getEventsInfo( $data['id_event'] );
			if( empty($event) ) return showStatus('No se encontró el evento pedido. Inténtelo nuevamente.');
			if( !canEditEvent(getSes('user'), $event['creator']) ) return oPermits()->noAccessMsg();
		}
		elseif( !oPermits()->can('createEvent') ) return oPermits()->noAccessMsg();
		
		# Pre-format input for comparing timestamps and querying
		$data['ini'] = "{$data['iniDate']} {$data['iniTime']}:00";
		$data['end'] = $data['endTime'] ? "{$data['iniDate']} {$data['endTime']}:00" : NULL;
		
		# Validation. Abort and inform user if any error occurred.
		$err = array();
		if( !$data['type'] ) $err = array('type', 'Debe indicarse el Tipo del evento.');
		if( !checkTimeStamp($data['ini']) ) $err = array('iniDate', 'Fecha/hora de inicio inválida.');
		if( $data['end'] !== NULL ){
			if( !checkTimeStamp($data['end']) ) $err = array('endTime', 'Hora de finalización inválida.');
			if( $data['ini'] > $data['end'] ) $err = array('endTime', 'La hora de inicio debe preceder a la de finalización');
		}
		if( !empty($err) ) return addScript("FTshowTip('evt_{$err[0]}', '{$err[1]}');");	/* Send error msg to the user */
		
		$info = array(
			'id_event'		=> $data['id_event'],
			'id_customer'	=> $data['id_customer'],
			'lastEdit'		=> array(
				'id_event'	=> $data['id_event'],		/* for editting, empty otherwise */
				'by'		=> getSes('user'),
			),
		);
		
		if( empty($data['target']) ) unset($data['target']);
		
		unset($data['iniDate'], $data['iniTime'], $data['endTime'],
			$data['id_event'], $data['id_customer'], $data['alarm']);
		
		oSQL()->setOkMsg('El evento fue guardado correctamente.');
		oSQL()->setErrMsg('Ocurrió un error al intentar guardar el evento.');
		if( !$id ) $data['creator'] = getSes('user');
		$ans = $id ? oSQL()->editEvent($data, $info) : oSQL()->createEvent($data, $info);
		
		if( $ans->error ) return showStatus( $ans->msg );
		else{
			$event = $id ? 'agendaEventEdited' : 'agendaEventCreated';
			$cutMsg = substr($data['event'], 0, 40).((strlen($data['event']) > 30) ? '...' : '');
			saveLog($event, ($id ? $id : $ans->ID), $cutMsg);
			return oNav()->getPage('agenda', array($data['ini']), $ans->msg, 1);
		}
		
	}

?>