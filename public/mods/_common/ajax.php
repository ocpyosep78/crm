<?php

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
				$msg = "El archivo subido debe ser una imagen con formato/extensión \'png\'.";
				return FileForm::addResponse("say('{$msg}');");
			}
		}

		# Set messages to return to the user after processing the request
		oSQL()->setOkMsg("La información fue correctamente actualizada.");
		oSQL()->setErrMsg("Ocurrió un error. Los cambios no fueron guardados.");
		# Request query and catch answer, then return it to the user
		$ans = oSQL()->editUsers($data);
		$page = 'usersInfo';

		# Save picture if one was chosen and data was stored
		if ($img['size'])
		{
			$imgpath = View::get('User')->image($atts['user']);

			if (!move_uploaded_file($img['tmp_name'], $imgpath))
			{
				$msg = "No se pudo guardar la imagen. Inténtelo nuevamente.";
				return FileForm::addResponse("say('{$msg}');");
			}
		}

		$atts = "['{$data['user']}']";

		return FileForm::addResponse("getPage('{$page}', {$atts}, '{$ans->msg}', '{$ans->successCode}');");
	}
	else
	{
		return FileForm::addResponse("showTip('editUsers_{$valid['field']}', '{$valid['tip']}');");
	}
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
		return say('Ocurrió un error al intentar cerrar el evento.');
	else {
		saveLog('agendaEventClosed', $id, $msg);
		return PageController::reload('El evento fue cerrado correctamente.', 1);
	}
}

function removeAlert($id) {

	$ans = oSQL()->removeAlert($id);

	return $ans->error ? say('No se pudo eliminar el evento. Inténtelo nuevamente.') : oXajaxResp();
}

function removeAllAlerts() {

	$ans = oSQL()->removeAllAlerts(getSes('user'));

	return $ans->error ? say('No se pudo eliminar el evento. Inténtelo nuevamente.') : oXajaxResp();
}

function createNotes($data, $modifier)
{
	# Strip prefix from data fields
	$pfx = 'notes_';
	oValidate()->preProcessInput($data, $pfx);

	# A few things depend on where we're using Notes list
	$page = $for = Controller::getPageParams()['page'];
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
		return say('Ocurrió un error. El elemento no pudo ser eliminado.');
}