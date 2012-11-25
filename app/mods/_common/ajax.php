<?php

return ['editUsers', # home, users
        'eventInfo', # agenda
        'closeAgendaEvent',
        'removeAlert',
        'removeAllAlerts',
        'createNotes',
        'deleteNotes'];

function editUsers($data)
{
	# Get rid of prefixes in $atts keys (remaining keys match field names in DB)
	oValidate()->preProcessInput($data, 'editUsers_');

	$img = $data['img'];
	unset($data['img']);

	if (($valid = oValidate()->test($data, 'users')) === true) {

		# Check image type and other attributes, if a new image was submitted
		if ($img['size']) {
			if (($imgAtts = getimagesize($img['tmp_name'])) === false || $imgAtts[2] != IMAGETYPE_PNG) {
				$msg = "El archivo subido debe ser una imagen con formato/extensi�n \'png\'.";
				return FileForm::addResponse("say('{$msg}');");
			}
		}

		# Set messages to return to the user after processing the request
		oSQL()->setOkMsg("La informaci�n fue correctamente actualizada.");
		oSQL()->setErrMsg("Ocurri� un error. Los cambios no fueron guardados.");
		# Request query and catch answer, then return it to the user
		$ans = oSQL()->editUsers($data);
		$isHome = oNav()->getCurrentModule() == 'home';
		$page = $isHome ? 'home' : 'usersInfo';

		# Save picture if one was chosen and data was stored
		if ($img['size'])
		{
			$imgpath = View::get('User')->image($atts['user']);

			if (!move_uploaded_file($img['tmp_name'], $imgpath))
			{
				$msg = "No se pudo guardar la imagen. Int�ntelo nuevamente.";
				return FileForm::addResponse("say('{$msg}');");
			}
		}

		if ($isHome)
		{
			foreach (oSQL()->getUser(getSes('user')) as $k => $v)
			{
				regSes($k, $v);
			}
		}

		$atts = $isHome ? '[]' : "['{$data['user']}']";

		return FileForm::addResponse("getPage('{$page}', {$atts}, '{$ans->msg}', '{$ans->successCode}');");
	}
	else
	{
		return FileForm::addResponse("showTip('editUsers_{$valid['field']}', '{$valid['tip']}');");
	}
}

function eventInfo($id)
{
	$types = oLists()->agendaEventTypes();
	$users = oLists()->users();

	$event = oSQL()->getEventsInfo($id);
	$event['type'] = $types[$event['type']];
	$event['user'] = $event['target'];
	$event['target'] = $event['user'] ? $users[$event['user']] : '';
	$event['event'] = nl2br($event['event']);

	oSmarty()->assign('event', $event);
	oSmarty()->assign('editions', oSQL()->getEventEditions($id));

	$dialogAtts = array('width' => 600,
	                    'title' => 'Informaci�n de Evento');

	return dialog('home/eventInfo.tpl', '#eventInfo', $dialogAtts);
}

function closeAgendaEvent($id, $msg, $rescheduled = false) {
	$data = array(
		'id_event' => $id,
		'user' => getSes('user'),
		'comment' => $msg,
		'rescheduled' => intval($rescheduled),
	);
	$ans = oSQL()->closeAgendaEvent($data);

	if ($ans->error)
		return say('Ocurri� un error al intentar cerrar el evento.');
	else {
		saveLog('agendaEventClosed', $id, $msg);
		return oNav()->reloadPage('El evento fue cerrado correctamente.', 1);
	}
}

function removeAlert($id) {

	$ans = oSQL()->removeAlert($id);

	return $ans->error ? say('No se pudo eliminar el evento. Int�ntelo nuevamente.') : oXajaxResp();
}

function removeAllAlerts() {

	$ans = oSQL()->removeAllAlerts(getSes('user'));

	return $ans->error ? say('No se pudo eliminar el evento. Int�ntelo nuevamente.') : oXajaxResp();
}

function createNotes($data, $modifier)
{
	# Strip prefix from data fields
	$pfx = 'notes_';
	oValidate()->preProcessInput($data, $pfx);

	# A few things depend on where we're using Notes list
	$page = $for = oNav()->getCurrentPage();
	# When on usersInfo page, messages are always for that user only
	if (strstr($page, 'users'))
		$data['user'] = $modifier;
	# When on customersInfo page, messages are always about that customer
	elseif (strstr($page, 'customers'))
		$data['id_customer'] = $modifier;

	# If visibility is used, it'll be either empty (public) or current user's ID (private)
	if (isset($data['visibility']))
		$data['user'] = $data['visibility'];

	# Get current note's ID if present (edition) and add who's creating/editing the note
	$id = $data['id_note'] = empty($data['SL_ID']) ? NULL : $data['SL_ID'];
	$data['by'] = getSes('user');

	# Unset all info that doesn't go in the database, and info that's useless if empty
	$nullIfEmpty = array('id_note', 'id_customer', 'user');
	foreach ($nullIfEmpty as $item)
		if (empty($data[$item]))
			$data[$item] = NULL;
	unset($data['SL_ID'], $data['date'], $data['visibility']);

	# Now validate the input and 1. save it on success, or 2. let the user know about it
	if (($valid = oValidate()->test($data, 'notes')) === true) {
		$ans = oSQL()->{$id ? 'update' : 'insert'}($data, '_notes', 'id_note');
		if (!$ans->error)
			return oTabs()->switchTab('notes');
		else
			return say('No se pudo procesar su consulta. ' .
							'Compruebe los datos ingresados y vuelva a intentarlo.');
	}
	else
		return addScript("showTip('{$pfx}{$valid['field']}', '{$valid['tip']}');");
}

function deleteNotes($id)
{
	$ans = oSQL()->delete('_notes', array('id_note' => $id));

	if (!$ans->error)
		return oTabs()->switchTab('notes');
	else
		return say('Ocurri� un error. El elemento no pudo ser eliminado.');
}