<?php


/**********************************/
/* H O M E
/**********************************/

function page_home()
{
	return SNP::snp('viewItem', 'User', ['id' => getSes('user')]);
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

function page_editAccInfo($user='')
{

	require_once(MODS_PATH . 'users/pages.php');

	return page_editUsers(getSes('user'));


}

function page_createEvent($id=NULL, $customerid=NULL){
	return page_editEvent($id, $customerid);
}

function page_editEvent($id=NULL, $customerid=NULL){		/* When $id is not given, it's a new event */
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
		$event['event'] = nl2br($event['event']);
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

function page_agendaDay($date=NULL, $currFilters=array(), $showRescheduled=1){
	if( !$date ) return oNav()->abortFrame('Faltan datos requeridos para cargar la página.');

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
	return SNP::snp('commonList', 'User');
}

function page_usersInfo($acc)
{
	$user = oSQL()->getUser($acc);

	if (empty($user))
	{
		$msg = 'No se encontró el usuario buscado.';
		return oNav()->redirectTo('users', NULL, $msg);
	}

	$id = $user['user'];
	$self = ($id == getSes('user'));
	$lastAccess = $self ? getSes('last_access') : $user['last_access'];

	# Make sure Tabs sees this page as users/usersInfo, even if it comes from home/home
	oTabs()->setPage( 'usersInfo' );

	# Block 'Personal'
	oFormTable()->clear();
	oFormTable()->addRow('Nombre', "{$user['name']} {$user['lastName']}");
	oFormTable()->addRow('Teléfono', $user['phone']);
	oFormTable()->addRow('Dirección', $user['address']);
	oFormTable()->addRow('Email', $user['email']);
	oFormTable()->addRow($self ? 'Acceso Previo' : 'Último Acceso', $lastAccess
		? date('d-m-Y H:i:s', strtotime($lastAccess))
		: "<span id='firstLoginMsg'>".($self ? 'Primer Login' : 'Nunca')."</span>");
	$blocks[] = oFormTable()->getTemplate();

	# Block 'Interno'
	oFormTable()->clear();
	oFormTable()->addRow('Usuario', $user['user']);
	oFormTable()->addRow('Perfil', $user['profile']);
	oFormTable()->addRow('Número', $user['employeeNum']);
	oFormTable()->addRow('Departamento', $user['department']);
	oFormTable()->addRow('Cargo', $user['position']);
	$blocks[] = oFormTable()->getTemplate();

	oSmarty()->assign('blocks', $blocks);

	oSmarty()->assign('isSelf', $self);
	oSmarty()->assign('userID', $id);
	oSmarty()->assign('userImg', getUserImg($id));
	oSmarty()->assign('sid', time());

	oSmarty()->assign('userData', oFormTable()->getTemplate());

	if ($self && (oNav()->realPage == 'home'))
	{
		oSmarty()->assign('comboList', NULL);
	}
	else
	{
		oLists()->includeComboList('users', NULL, $user['user']);
	}

	return oTabs()->start();
}

function page_editUsers($acc=NULL)
{
	if ($acc)   // Edit mode
	{
		$user = oSQL()->getUser($acc);
		if (empty($user))
		{
			$msg = 'Usuario no encontrado.';
			return oNav()->loadContent('users', array(), $msg);
		}

		if ($user['id_profile'] < getSes('id_profile'))
		{
			$msg = 'No es posible editar usuarios con perfil más alto que el suyo.';
			return oNav()->goBack($msg);
		}
	}

	# See if we're editing self account
	$self = ($acc == getSes('user'));

	# Whether we're editing (or creating a new user)
	$edit = !empty($user);

	oFormTable()->clear();
	oFormTable()->setPrefix( $edit ? 'editUsers_' : 'createUsers_' );

	# Block 'Cuenta'
	if( !$edit || !$self ) oFormTable()->addTitle( 'Cuenta' );

	if( !$edit ){
		oFormTable()->addInput('Usuario', array('id' => 'user'));
		oFormTable()->addInput('Contraseña', array('id' => 'pass'), 'password');
	}
	else{
		oFormTable()->hiddenRow();
		oFormTable()->addInput('', array('id' => 'user'), 'hidden');
	}

	if( !$self ){
		oFormTable()->addCombo('Perfil',
			array('' => '(seleccionar)') + oLists()->profiles( getSes('id_profile') ),
			array('id' => 'id_profile') );
	}

	# Block 'Personal'
	oFormTable()->addTitle( 'Personal' );
	oFormTable()->addInput('Nombre', array('id' => 'name'));
	oFormTable()->addInput('Apellidos', array('id' => 'lastName'));
	oFormTable()->addInput('Teléfono', array('id' => 'phone'));
	oFormTable()->addInput('Dirección', array('id' => 'address'));
	oFormTable()->addInput('Email', array('id' => 'email'));

	oFormTable()->addTitle( '' );
	oFormTable()->addFile('Imagen', array('id' => 'img'), $edit ? 'editUsers' : 'createUsers');
	oFormTable()->addTitle( '' );

	# Block 'Interno'
	oFormTable()->addTitle( 'Interno' );
	oFormTable()->addInput('Número', array('id' => 'employeeNum'));
	oFormTable()->addCombo('Departamento',
		array('' => '(seleccionar)') + oLists()->departments(),
		array('id' => 'id_department'));
	oFormTable()->addInput('Cargo', array('id' => 'position'));

	if ($edit)
	{
		# Block 'Información'
		oFormTable()->addTitle( 'Información' );
		oFormTable()->addRow('Último Acceso', $user['last_access']
			? date('d-m-Y H:i:s', strtotime($user['last_access']))
			: "<span style='color:#600000; font-size:12px; font-weight:bold'>Nunca</span>"
		);

		oFormTable()->fillValues($user);
	}

	# Submit line
	oFormTable()->addSubmit($edit ? 'Guardar Cambios' : 'Guardar');

	# Add commands and actions to Xajax response object
	oNav()->updateContent(oFormTable()->getTemplate(), true);

	$prefix = $edit ? 'editUsers' : 'createUsers';
	return addScript("\$('#{$prefix}_user').focus();");
}

function page_createUsers()
{
	return page_editUsers();
}


/**********************************/
/* C U S T O M E R S
/**********************************/

function page_customers()         { return SNP::snp('commonList', 'Customer.Confirmed'); }
function page_potentialCustomers(){ return SNP::snp('commonList', 'Customer.Potential'); }
function page_createCustomers()   { return SNP::snp('createItem', 'Customer'); }
function page_customersInfo($id)  { return SNP::snp('simpleItem', 'Customer', ['id' => $id]); }

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

function page_products() { return SNP::snp('commonList', 'Product.Regular'); }
function page_materials(){ return SNP::snp('commonList', 'Product.Material'); }
function page_services() { return SNP::snp('commonList', 'Product.Service'); }
function page_others()   { return SNP::snp('commonList', 'Product.Others'); }

function page_createProducts() { return SNP::snp('createItem', 'Product.Regular'); }
function page_createMaterials(){ return SNP::snp('createItem', 'Product.Material'); }
function page_createServices() { return SNP::snp('createItem', 'Product.Service'); }
function page_createOthers()   { return SNP::snp('createItem', 'Product.Others'); }

function page_productsInfo($id){ return SNP::snp('viewItem', 'Product', ['id' => $id]); }


/**********************************/
/* E S T I M A T E S
/**********************************/

function page_createEstimates_pack($modifier=NULL)
{
	oSmarty()->assign('customers', oLists()->customers());
}

function page_estimates_pack($modifier=NULL)
{
	return oSnippet()->addSnippet('commonList', 'estimates_pack', $modifier);
}

function page_editEstimates_pack($id)
{
	page_estimates_packInfo($id);

	return oNav()->updateContent('estimates/estimates_packInfo.tpl');
}

function page_estimates_packInfo($id)
{
	oSmarty()->assign('id', $id);
	oNav()->setJSParams($id);

	$info = oSnippet()->getSnippet('viewItem', 'estimates_pack', array('filters' => $id));
	oSmarty()->assign('info', $info);

	$data = oSQL()->getEstimatesDetail(array('pack' => array($id, '=')));

	foreach($data as &$v)
	{
		$v['utility'] = (float)$v['cost']
			? number_format(($v['price']/$v['cost'])*100-100, 2)
			: '--';
	}

	oSmarty()->assign('data', $data);

	$left = oSQL()->estimatesLeft($id);
	oSmarty()->assign('left', $left);
}

/**
 * Serves to generate both estimates and quotes lists
 * depending on $modifier
 */
function page_estimates($modifier='estimates')
{
	return oLists()->printList('estimates', $modifier);
}

function page_quotes()
{
	return oLists()->printList('estimates', 'quotes');
}

/**
 * Reused for createEstimates and editEstimates pages depending on $id parameter
 */
function page_createEstimates($estimate=NULL, $packID=NULL)
{
	if (empty($estimate))
	{
		$estimate = NULL;
	}

	$id = empty($estimate['id_estimate']) ? NULL : $estimate['id_estimate'];
	$isNew = !$id;
	$isQuote = isQuote($estimate);
	$modifier = $isQuote ? 'quotes' : 'estimates';

	oSmarty()->assign('edit', true);
	oSmarty()->assign('isNew', $isNew);
	oSmarty()->assign('isQuote', $isQuote);

	$type = $isNew ? 'Nombre' : ($isQuote ? 'Cotización' : 'Presupuesto');
	oSmarty()->assign('estimateType', $type);

	$common = estimates_commonData();
	oSmarty()->assign('data', $common);

	$system = 1;
	if ($estimate)
	{
		foreach ($estimate['detail'] as $row)
		{
			if ($row['id_system'])
			{
				$system = $row['id_system'];
			}
		}
	}

	oSmarty()->assign('system', $estimate['id_system'] ? $estimate['id_system'] : $system);

	# Get pack info if estimate belongs to a pack
	if (empty($packID) && $estimate['pack'])
	{
		$packID = $estimate['pack'];
	}

	if ($packID)
	{
		$selected = array('id_estimates_pack' => $packID);
		$pack = oSQL()->doselect('estimates_pack', '*', $selected, 'row');
	}

	oSmarty()->assign('pack', $packID ? $pack : NULL);

	# Initialize estimate keys in case it comes empty
	if (empty($estimate))
	{
		$estimate = ['orderNumber' => '',
		             'id_estimate' => '',
		             'estimate'    => '',
		             'id_customer' => '',
		             'customer'    => '',
		             'id_system'   => '',
		             'system'      => '',
		             'pack'        => $packID ? $packID : ''];

		oSmarty()->assign('estimate', $estimate);
	}

	addScript('window.taxes = 0.22;');

	# Include a comboList
	oLists()->includeComboList('estimates', $modifier, $id);

	/* Return content so it doesn't fail when called as page_editEstimates */
	return oNav()->updateContent( 'estimates/createEstimates.tpl' );
}

function page_estimatesInfo($id, $estimate=NULL)
{
	/* Estimate could come prefetched (in the case of on-the-fly estimatesInfo) */
	if (!$estimate)
	{
		$estimate = getEstimate($id);
	}

	# Get pack info if estimate belongs to a pack
	$packID = $estimate['pack'];
	$pack = $packID ? oSQL()->doselect('estimates_pack', '*', array('id_estimates_pack' => $packID), 'row') : NULL;
	oSmarty()->assign('pack', $pack);

	$isQuote = isQuote( $estimate );
	$modifier = $isQuote ? 'quotes' : 'estimates';

	oSmarty()->assign('miniHeader', false);
	oSmarty()->assign('edit', false);
	oSmarty()->assign('isQuote', $isQuote);
	oSmarty()->assign('estimateType', $isQuote ? 'Cotización' : 'Presupuesto');

	if( empty($estimate) ){		/* Something's wrong, this array should have all info */
		return oNav()->getPage('estimates', array(), 'No se encontró el presupuesto pedido.');
	}

	$system = 1;
	$taxes = 0.22;
	$totals = array('subTotal' => 0, 'tax' => 0, 'total' => 0);
	foreach( $estimate['detail'] as $key => $item ){
		$estimate['detail'][$key]['subTotal'] = $item['amount'] * $item['price'];
		$estimate['detail'][$key]['tax'] = $item['amount'] * $item['price'] * $taxes;
		$estimate['detail'][$key]['total'] = $item['amount'] * $item['price'] * ($taxes + 1);
		$totals['subTotal'] += $estimate['detail'][$key]['subTotal'];
		$totals['tax'] += $estimate['detail'][$key]['tax'];
		$totals['total'] += $estimate['detail'][$key]['total'];
		if( $item['id_system'] ) $system = $item['id_system'];
	}
	oSmarty()->assign('system', $estimate['id_system'] ? $estimate['id_system'] : $system);

	oSmarty()->assign('data', $estimate);
	oSmarty()->assign('totals', $totals);

	# Include a comboList
	oLists()->includeComboList('estimates', $modifier, $id);
}

function page_editEstimates($id)
{
	$estimate = getEstimate($id);

	if( empty($estimate) )		/* Something's wrong, this array should have all info */
	{
		return oNav()->getPage('estimates', array(), 'No se encontró el presupuesto pedido.');
	}

	addScript('window.estimateDetail = '.toJson($estimate['detail']));

	return page_createEstimates( $estimate );
}

/**
 * Design work plan for installing presented estimate
 */
function page_installPlan($id, $product=NULL)
{
	oNav()->setJSParams($id);

	oSmarty()->assign('id_estimate', $id);
	oSmarty()->assign('data', oSQL()->getInstallPlan($id));
	oSmarty()->assign('products', oSQL()->estimateProducts($id));
	oSmarty()->assign('product', $product);
}

/**
 * Creates a PDF for an estimate (shown within frame)
 * First screen shows the already configured final steps, and those pending,
 * including workPlan (installPlan), pictures, OS (if server included), etc.
 */
function page_estimatePDF($id, $straightToPDF=false)
{
	oNav()->setJSParams( $id );
	oSmarty()->assign('path', EXPORT_PDF_PATH."estimate.php?id={$id}");
}


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

	if (oPermits()->can('adminTechNotes'))
	{
		oSmarty()->assign('adminNote', oSql()->getAdminTechNote($id));
	}

	oNav()->setJSParams($id);
}


/**********************************/
/* C O N F I G
/**********************************/

/**
 * Config has only one real enter point, page config. There it shows several
 * tabs (depending on user's permissions), and each links to a subpage. Each
 * of these subpages has an associated permission named subCfg{$code}. Default
 * config subpage's code is 'Main' (it has no permission associated, other than
 * 'config', as it's the main page of the module).
 *
 * This function discriminates calls to each tab, and delivers responsability
 * to functions subCfg{$code}, located in mods/config/funcs.php.
 *
 * In general lines, responsible functions will define call methods of class
 * Config [called as oMod()]. Back to page_config, the page will
 * be presented, with chosen tab selected and content defined by subpage's code.
 *
 * Content is found in {TEMPLATES_PATH}/config/tab{$code}.
 */
function page_config($code='Main')
{
	# Clear permissions, modules, pages, areas, from cache (to stay updated always)
	oPermits()->clear();

	# Get list of available tabs, and make sure it's available (or fall down to default)
	if (!oConfig()->tabExists($code))
	{
		$code = 'Profiles';//Main';
	}

	# Call responsible function to build subPage (content for this tab, that is)
	if ($code != 'Main' && oPermits()->can($tab="subCfg{$code}"))
	{
		$tab();
	}
	else
	{
		return oPermits()->noAccessMsg();
	}

	oSmarty()->assign('tabs', oConfig()->getTabs());
	oSmarty()->assign('tab', $code);

	addScript("TAB = '{$code}';");
}