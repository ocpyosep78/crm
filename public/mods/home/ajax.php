<?php

return array('editAcc',
             'createEvent',
             'closeActivityEntry');


function editAcc($atts)
{

	# Get rid of prefixes in $atts keys (remaining keys match field names in DB)
	oValidate()->preProcessInput($atts, 'editAcc_');
	$valid = oValidate()->test(array('pass' => $atts['newPass1']), 'users');

	# If an error occurred, set $err = array(FieldWithError, ErrorTip)
	if (!$atts['oldPass'])
	{
		$err = array('oldPass', 'Debe escribir la contraseña actual.');
	}
	elseif (md5($atts['oldPass']) != getSes('pass'))
	{
		$err = array('oldPass', 'La contraseña actual no es correcta.');
	}
	elseif (!$atts['newPass1'])
	{
		$err = array('newPass1', 'Debe escribir su nueva contraseña 2 veces.');
	}
	elseif (!$atts['newPass2'])
	{
		$err = array('newPass2', 'Debe escribir su nueva contraseña 2 veces.');
	}
	elseif ($atts['newPass1'] != $atts['newPass2'])
	{
		$err = array('newPass2', 'Las contraseñas nuevas no coinciden.');
	}
	elseif ($valid !== true)
	{
		$err = array('newPass1', $valid['tip']);
	}

	if (isset($err))
	{
		return addScript("showTip('editAcc_{$err[0]}', '{$err[1]}');");
	}

	# Set message to return to the user after processing the request, if error
	oSQL()->setErrMsg("Ocurrió un error. Su contraseña no fue modificada.");

	# Request query and catch answer, then return it to the user
	$data = array('user' => getSes('user'), 'pass' => md5($atts['newPass1']));

	$ans = oSQL()->editUsers($data);

	if ($ans->error)
	{
		return say($ans->msg, $ans->successCode);
	}
	elseif (!$ans->rows)
	{
		$msg1 = 'No se han guardado cambios. Verifique los datos ingresados.';
		$msg2 = 'Es posible que su cuenta no disponga de permisos suficientes.';
		return say("{$msg1}<br />{$msg2}");
	}
	else
	{
		return logout('Inicie sesión con su nueva contraseña.', 2);
	}

}

function createEvent($data, $id=NULL)
{
	$id && Access::enforce('editEvent');

	if (!empty($data['remind']) && !empty($data['reminder']))
	{
		$reminder = array(
			'config' => array('model' => 'events', 'time' => $data['reminder']),
			'users' => $data['remind']
		);
	}

	unset($data['remind'], $data['reminder']);

	oValidate()->preProcessInput($data, 'evt_');

	# Check that the user has required permissions
	if ($id)
	{
		$event = oSQL()->getEventsInfo($data['id_event']);

		if (empty($event))
		{
			$msg = 'No se encontró el evento pedido. Inténtelo nuevamente.';
			throw new PublicException($msg);
		}

		Access::enforce(canEditEvent(getSes('user'), $event['creator']));
	}
	else
	{
		Access::enforce('createEvent');
	}

	# Pre-format input for comparing timestamps and querying
	$data['ini'] = "{$data['iniDate']} {$data['iniTime']}:00";
	$data['end'] = $data['endTime'] ? "{$data['iniDate']} {$data['endTime']}:00" : NULL;

	# Validation. Abort and inform user if any error occurred.
	$err = array();

	if (!$data['type'])
	{
		$err = array('type', 'Debe indicarse el Tipo del evento.');
	}
	elseif (!checkTimeStamp($data['ini']))
	{
		$err = array('iniDate', 'Fecha/hora de inicio inválida.');
	}
	elseif ($data['end'] !== NULL)
	{
		if (!checkTimeStamp($data['end']))
		{
			$err = array('endTime', 'Hora de finalización inválida.');
		}
		elseif ($data['ini'] > $data['end'])
		{
			$err = array('endTime', 'La hora de inicio debe preceder a la de finalización');
		}
	}

	if (!empty($err))
	{
		return addScript("showTip('evt_{$err[0]}', '{$err[1]}');");
	}

	$info = array(
		'id_event'		=> $data['id_event'],
		'id_customer'	=> $data['id_customer'],
		'lastEdit'		=> array(
			'id_event'	=> $data['id_event'],		/* for editing, empty otherwise */
			'by'		=> getSes('user'),
		),
	);

	if (empty($data['target']))
	{
		unset($data['target']);
	}

	unset($data['iniDate'], $data['iniTime'], $data['endTime'],
		  $data['id_event'], $data['id_customer'], $data['alarm']);

	oSQL()->setOkMsg('El evento fue guardado correctamente.');
	oSQL()->setErrMsg('Ocurrió un error al intentar guardar el evento.');

	if ($id)
	{
		$ans = oSQL()->editEvent($data, $info);
	}
	else
	{
		$data['creator'] = getSes('user');
		$ans = oSQL()->createEvent($data, $info);
	}

	if ($ans->error)
	{
		return say( $ans->msg );
	}
	else
	{
		$key = $id ? $id : $ans->ID;
		$event = $id ? 'agendaEventEdited' : 'agendaEventCreated';
		$cutMsg = substr($data['event'], 0, 40).((strlen($data['event']) > 30) ? '...' : '');
		saveLog($event, $key, $cutMsg);

		# Save activity record for technical and sales
		$technical = array('install', 'laststeps', 'remote', 'service', 'technical');
		$sales = array('incomes', 'delivery', 'invoice', 'travel', 'estimate', 'sales');

		if (in_array($data['type'], array_merge($technical, $sales)))
		{
			oSQL()->insert(array('model' => 'events', 'uid' => $key), 'activity');
		}

		# Remove previous reminders for this event (if any)
		oSQL()->delete('reminders', array('model' => 'events', 'object' => $key));

		# Save reminder
		if (!empty($reminder))
		{
			$reminder['config']['object'] = $key;
			$ans = oSQL()->insert($reminder['config'], 'reminders');

			if (!$ans->error && $ans->ID)
			{
				foreach ($reminder['users'] as $user)
				{
					$row = array('id_reminder' => $ans->ID, 'user' => $user);
					oSQL()->insert($row, 'reminders_users');
				}
			}
		}

		Controller::redirect('agenda', array($data['ini']), $ans->msg, 1);
	}
}

function closeActivityEntry($id)
{
	oSQL()->delete('activity', array('id' => $id));

	return oNav()->reloadPage();
}