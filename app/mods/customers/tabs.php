<?php

	$id = array_shift($aux=oNav()->getCurrentAtts());
	$filter = array('id_customer' => $id);

	# If user has permit usersNotes, show all. Else, show only his own notes on the customer.
	$notesFilter = Access::can('usersNotes')
		? $filter
		: $filter + array('user' => getSes('user'));

	$cnt = array(
		'notes'         => oStats()->count('_notes', $notesFilter),
		'agenda'        => oStats()->countAgendaEvents( $filter ),
//		'contacts'      => oStats()->count('customers_contacts', $filter),
		'owners'        => oStats()->count('customers_owners', $filter),
		'estimates'     => oStats()->count('estimates', $filter),
		'sales'         => oStats()->count('sales', $filter + array('type' => 'sale')),
		'installs'      => oStats()->count('sales', $filter + array('type' => 'install')),
		'techVisits'    => oStats()->count('sales', $filter + array('type' => 'service')),
	);

	return array(
		'notes'         => "Notas ({$cnt['notes']})",
		'agenda'        => array('name' => "Agenda ({$cnt['agenda']})", 'permit' => 'agenda'),
//		'contacts'      => "Contactos ({$cnt['contacts']})",
		'owners'        => "Titulares ({$cnt['owners']})" ,
		'estimates'     => array('name' => "Presupuestos ({$cnt['estimates']})", 'permit' => 'estimates'),
		'sales'         => array('name' => "Ventas ({$cnt['sales']})", 'permit' => 'sales'),
		'installs'      => "Instalaciones ({$cnt['installs']})",
		'techVisits'    => array('name' => "Técnica ({$cnt['techVisits']})", 'permit' => 'techVisits'),
//		'history'       => 'Historial',
	);



	/**
	 * listsByCustomer() holds code common to several tabs in customersInfo page (right below it)
	 */
	function tab_customersInfo_notes( $id ){

		$permits = array('createNotes', 'editNotes', 'deleteNotes');
		Access::setAlias('customersInfo', $permits);

		oLists()->hasCombo( false );
		oLists()->setSource("notesByCustomer");
		oLists()->addComboOptions('id_customer', array('' => '') + oLists()->customers());
		oLists()->addComboOptions('type', array('' => '', 'technical' => 'Técnica', 'sales' => 'Ventas'));
		oLists()->addComboOptions('visibility', array(getSes('user') => 'Privado', '' => 'Público'));

		# For users with permit usersNotes, we show all notes. For others, just their own.
		$ids = Access::can('usersNotes') ? $id : "{$id}__|__".getSes('user');
		oTabs()->useThisHTML( oLists()->simpleListHTML('notes', $ids) );

		return "initializeSimpleList();";

	}

	/**
	 * Tab functions are not called directly. They are called automatically by object Tabs.
	 */

	function tab_customersInfo_contacts( $id ){

		$permits = array('createCustomerContacts', 'editCustomerContacts', 'deleteCustomerContacts');
		Access::setAlias('editCustomers', $permits);
		oTabs()->useThisHTML( oLists()->simpleListHTML('customerContacts', $id) );

		return "initializeSimpleList('customerContacts', '{$id}');";

	}

	function tab_customersInfo_owners( $id ){

		$permits = array('createCustomerOwners', 'editCustomerOwners', 'deleteCustomerOwners');
		Access::setAlias('editCustomers', $permits);
		oTabs()->useThisHTML( oLists()->simpleListHTML('customerOwners', $id) );

		return "initializeSimpleList('customerOwners', '{$id}');";

	}

	function tab_customersInfo_agenda($id)
	{
		$events = oSQL()->getCustomerEvents($id);

		oSmarty()->assign('types', oLists()->agendaEventTypes());
		oSmarty()->assign('events', $events);
	}

	/**
	 * listsByCustomer() holds code common to several tabs in customersInfo page (right below it)
	 */
	function listsByCustomer($id, $code){

		oLists()->hasCombo( false );
		oLists()->setSource("{$code}ByCustomer");

		oTabs()->useThisHTML( oLists()->listHTML($code, $id) );

		return "initializeList('{$code}', '{$id}', '{$code}ByCustomer');";

	}
	function tab_customersInfo_estimates( $id )	{	return listsByCustomer($id, 'estimates');	}
	function tab_customersInfo_sales( $id )		{	return listsByCustomer($id, 'sales');		}
	function tab_customersInfo_installs( $id )	{	return listsByCustomer($id, 'installs');	}
	function tab_customersInfo_techVisits( $id ){	return listsByCustomer($id, 'techVisits');	}

	function tab_customersInfo_history( $id ){

	}