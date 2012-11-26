<?php


/**********************************/
/* H O M E
/**********************************/

function page_home()
{
	return page_agenda();
}

function page_editAcc()
{
	$user = oSQL()->getUser(getSes('user'));

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
	return addScript("\$('#editAcc_oldPass').focus();");

}

function page_createEvent($id=NULL, $customerid=NULL)
{
	return page_editEvent($id, $customerid);
}

function page_editEvent($id=NULL, $customerid=NULL)
{
	$event = $id ? oSQL()->getEventsInfo($id) : array();
	if($id && empty($event)) return oNav()->getPage('agenda', array(), 'Evento no encontrado.');

	if( !empty($event) ){		# Fix data to fit fields organization upon editing
		$event['iniDate'] = substr($event['ini'], 0, 10);
		$event['iniTime'] = substr($event['ini'], 11, 5);
		$event['endTime'] = $event['end'] ? substr($event['end'], 11, 5) : '';
		unset($event['ini'], $event['end']);
	}
	elseif (!empty($customerid)){
		$event['id_customer'] = $customerid;
	}

	$users = oLists()->users();
	oSmarty()->assign('users', $users);

	# Reminders
	$remindees = array();
	if( $id && $event['id_reminder'] ){
		$filter = array('id_reminder' => $event['id_reminder']);
		$remindees = oSQL()->doselect('reminders_users', 'user', $filter, 'col');
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
	if( $id ) oFormTable()->fillValues( $event );		# Fill table with values (editing)
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

	if( $event ) oFormTable()->fillValues( $event );		# Fill table with values (editing)


	oSmarty()->assign('optional', oFormTable()->getTemplate());

	oSmarty()->assign('id_event', $id ? $id : '');

	return oNav()->updateContent('home/editEvent.tpl');

}

function page_agenda($firstDay=NULL, $currFilters=array(), $showRescheduled=1)
{
	/* If $firstDay is not given, start on last Monday */
	empty($firstDay) && ($firstDay = 0);

	/* If it's given as a date, or the format is wrong */
	if (!is_numeric($firstDay))
	{
		$dayNum = strtotime($firstDay);
		$firstDay = $dayNum ? ceil(($dayNum - time()) / 86400) : 0;
	}

	while (date('N', strtotime("{$firstDay} days")) != 1)
	{
		$firstDay--;
	}

	foreach ($currFilters as $key => $filter)
	{
		if (empty($filter))
		{
			unset($currFilters[$key]);
		}
	}

	$range = ['ini' => date('Y-m-d', strtotime("{$firstDay} days")),
	          'end' => date('Y-m-d', strtotime(($firstDay + AGENDA_DAYS_TO_SHOW - 1).' days'))];
	$events = oSQL()->getEventsInfo(NULL, $range, $currFilters);

	# Get data and pre-process it
	$data = array();

	for ($i=$firstDay; $i < ($firstDay + AGENDA_DAYS_TO_SHOW); $i++)
	{
		$date = date('Y-m-d', strtotime("{$i} days"));
		$data[$date] = ['date'    => $date,
		                'isToday' => !$i,
		                'events'  => []];
	}

	# Fill days with events
	foreach ($events as $event)
	{
		$event['event'] = nl2br($event['event']);
		$data[substr($event['ini'], 0, 10)]['events'][] = $event;
	}

	foreach ($data as $day)
	{
		$days[] = $day;
	}

	# Filters
	$filters = [
		'type' => ['name'    => 'Tipo',
		           'options' => ['' => '(todos)'] + oLists()->agendaEventTypes()],
		'user' => ['name'    => 'Usuario',
		           'options' => [''=>'(todos)'] + oLists()->users()]
	];

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

function page_agendaDay($date=NULL, $currFilters=array(), $showRescheduled=1)
{
	if (!$date)
	{
		return oNav()->abortFrame('Faltan datos requeridos para cargar la página.');
	}

	# Basic structure of data to be passed
	$day['date'] = $date;
	$day['isToday'] = true;
	$day['events'] = oSQL()->getEventsInfo(NULL, $date, $currFilters);

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

	# Fill day with events
	foreach ($day['events'] as &$event)
	{
		$event['event'] = nl2br($event['event']);
	}

	oSmarty()->assign('day', $day);
	oSmarty()->assign('types', oLists()->agendaEventTypes());
	oSmarty()->assign('data', array(array('date' => $date)));
	oSmarty()->assign('filters', $filters);
	oSmarty()->assign('currFilters', $currFilters + array_fill_keys(array_keys($filters), ''));
	oSmarty()->assign('showRescheduled', $showRescheduled);
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


/**********************************/
/* U S E R S
/**********************************/

function page_users()
{
	return Snippet::snp('commonList', 'User');
}

function page_usersInfo($id)
{
	return Snippet::snp('viewItem', 'User', ['id' => $id]);
}

function page_editUsers($id)
{
	return Snippet::snp('editItem', 'User', ['id' => $id]);
}

function page_createUsers()
{
	return Snippet::snp('createItem', 'User');
}


/**********************************/
/* C U S T O M E R S
/**********************************/

function page_customers()         { return Snippet::snp('commonList', 'Customer.Confirmed'); }
function page_potentialCustomers(){ return Snippet::snp('commonList', 'Customer.Potential'); }
function page_createCustomers()   { return Snippet::snp('createItem', 'Customer'); }
function page_customersInfo($id)  { return Snippet::snp('simpleItem', 'Customer', ['id' => $id]); }

function page_sales()
{
	return oLists()->printList('sales', 'sale');
}

function page_registerSales()
{
	/* TEMP: it should always show current date, but for now we're registering OLD sales */
	oSmarty()->assign('tmpDate', isset($_GET['f']) ? "{$_GET['f']}-01" : date('Y-m-d'));
}


/**********************************/
/* P R O D U C T S
/**********************************/

function page_products() { return Snippet::snp('commonList', 'Product.Regular'); }
function page_materials(){ return Snippet::snp('commonList', 'Product.Material'); }
function page_services() { return Snippet::snp('commonList', 'Product.Service'); }
function page_others()   { return Snippet::snp('commonList', 'Product.Others'); }

function page_createProducts() { return Snippet::snp('createItem', 'Product.Regular'); }
function page_createMaterials(){ return Snippet::snp('createItem', 'Product.Material'); }
function page_createServices() { return Snippet::snp('createItem', 'Product.Service'); }
function page_createOthers()   { return Snippet::snp('createItem', 'Product.Others'); }

function page_productsInfo($id){ return Snippet::snp('viewItem', 'Product', ['id' => $id]); }


/**********************************/
/* T E C H N I C A L
/**********************************/

function page_technical()
{
	return page_techVisits();
}

function page_techVisits(){
	/* Include code in case it's called with an alias */
	return oLists()->printList('techVisits');
}

function page_installs()
{
	return oLists()->printList();
}

function page_createTechVisits($id=NULL, $customerid=NULL)
{
	# If an id was provided, we pass the visit's data, to pre-fill the form (edit/info mode)
	if (!$id && !$customerid)
	{
		$date = explode('-', date('Y-m-d'));
		oNav()->setJSParams( array(
			'day'	=> $date[2],
			'month'	=> $date[1],
			'year'	=> $date[0],
		) );
	}
	elseif (!empty($customerid))
	{
		$data['id_customer'] = $customerid;
		oNav()->setJSParams( $data );
	}
	/* If we're editing, get data and fix special fields */
	elseif ($data=oSQL()->getTechVisit($id))
	{
		list($data['year'], $data['month'], $data['day']) = explode('-', $data['date']);

		if (!empty($data['installDate']))
		{
			# Part install date and fix warranty (from warranty months to warranty void date)
			list($data['installYear'], $data['installMonth'], $data['installDay']) = explode('-', $data['installDate']);
			$data['warranty'] = strtotime($data['installDate'].' + '.$data['warranty'].' months') > time() ? 1 : 0;
		}
		else
		{
			$data['warranty'] = 0;
		}

		if (!empty($data['starts']))
		{
			list($data['startsH'], $data['startsM']) = explode(':', $data['starts']);
		}

		if (!empty($data['ends']))
		{
			list($data['endsH'], $data['endsM']) = explode(':', $data['ends']);
		}

		$data['costDollars'] = $data['currency'] == 'U$S' ? $data['cost'] : '';
		$data['cost'] = $data['currency'] == '$' ? $data['cost'] : '';

		oNav()->setJSParams( $data );
	}
	else
	{
		return oNav()->getPage('techVisits', 'No se encontró la visita pedida.');
	}

	oSmarty()->assign('systems', oLists()->systems());
	oSmarty()->assign('technicians', oLists()->technicians());

	hideMenu();
}

function page_editTechVisits($id)
{
	return page_createTechVisits($id);
}

function page_techVisitsInfo($id)
{
	oSmarty()->assign('id', $id);

	if (Access::can('adminTechNotes'))
	{
		oSmarty()->assign('adminNote', oSql()->getAdminTechNote($id));
	}

	oNav()->setJSParams($id);
}