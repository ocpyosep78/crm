<?php

$id = array_shift($aux=oNav()->getCurrentAtts());

$cnt = array(
	'notes'			=> oStats()->count('_notes', array('user' => $id)),
	'agendaBy'		=> oStats()->countAgendaEvents( array('creator' => $id) ),
	'agendaFor'		=> oStats()->countAgendaEvents( array('target' => $id) ),
	'customers'		=> oStats()->count('customers', array('seller' => $id)),
);

# Do not show customers if list is empty (to hide it we change the required permit to devel)
$showCustomers = !!$cnt['customers'] ? 'customers' : 'devel';
# Show Notes always for current user, but request permit userNotes for other users' notes
$showNotes = ($id == getSes('user')) ? 'home' : 'usersNotes';

return array(
	'notes'		=> array('name' => "Notas ({$cnt['notes']})", 'permit' => $showNotes),
	'agendaBy'	=> array('name' => "Agenda (de {$id}) ({$cnt['agendaBy']})", 'permit' => 'agenda'),
	'agendaFor'	=> array('name' => "Agenda (para {$id}) ({$cnt['agendaFor']})", 'permit' => 'agenda'),
	'customers'	=> array('name' => "Clientes del Usuario ({$cnt['customers']})", 'permit' => $showCustomers),
);

function tab_usersInfo_agendaBy($id, $src='by')
{
	$user = oSQL()->getUser($id);
	$events = oSQL()->getUserEvents($user['user'], $src);

	oSmarty()->assign('types', oLists()->agendaEventTypes());
	oSmarty()->assign('events', $events);

	oTabs()->useThisTemplate('agenda');
}

function tab_usersInfo_agendaFor($id)
{
	return tab_usersInfo_agendaBy($id, 'for');
}

/**
 * listsByCustomer() holds code common to several tabs in customersInfo page (right below it)
 */
function tab_usersInfo_customers( $id ){

	oLists()->hasCombo( false );
	oLists()->setSource('customersByUser');

	oTabs()->useThisHTML( oLists()->listHTML('customers', $id) );

	return "initializeList('customers', '{$id}', 'customersByUser');";

}

/**
 * listsByCustomer() holds code common to several tabs in customersInfo page (right below it)
 */
function tab_usersInfo_notes( $id ){

	$permits = array('createNotes', 'editNotes', 'deleteNotes');
	oPermits()->setAlias('home', $permits);

	oLists()->hasCombo( false );
	oLists()->setSource("notesByUser");
	oLists()->addComboOptions('id_customer', array('' => '') + oLists()->customers());
	oLists()->addComboOptions('type', array('' => '', 'technical' => 'Técnica', 'sales' => 'Ventas'));

	oTabs()->useThisHTML( oLists()->simpleListHTML('notes', $id) );

	return "initializeSimpleList();";

}