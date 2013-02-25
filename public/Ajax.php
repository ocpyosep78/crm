<?php

return ['editUsers', # home, users
        'eventInfo', # agenda
        'closeAgendaEvent',
        'removeAlert',
        'removeAllAlerts',
        'createNotes',
        'deleteNotes'];

class Ajax extends BaseAjax
{

	public static function eventInfo($id)
	{
		$types = View::get('EventType')->getHashData();
		$users = View::get('User')->getHashData();

		$event = oSQL()->getEventsInfo($id);
		$event['type'] = $types[$event['type']];
		$event['user'] = $event['target'];
		$event['target'] = $event['user'] ? $users[$event['user']] : '';
		$event['event'] = nl2br($event['event']);

		Template::one()->assign('event', $event);
		Template::one()->assign('editions', oSQL()->getEventEditions($id));

		$dialogAtts = array('width' => 600,
							'title' => 'Información de Evento');

		return dialog('home/eventInfo.tpl', '#eventInfo', $dialogAtts);
	}

}