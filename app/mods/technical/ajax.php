<?php

return array('tchFormSuggest',
             'tchFormAcceptSale',
             'createTechVisit',
             'saveAdminTechNotes');


function saveAdminTechNotes($id, $note)
{
	if (Access::can('adminTechNotes'))
	{
		$note = addslashes($note);
		$res = oSQL()->saveAdminTechNote($id, $note)->successCode;
	}
	else
	{
		$res = NULL;
	}

	return say($res ? 'Nota guardada con éxito' : 'Ocurrió un error al guardar la nota', !!$res);
}

function tchFormSuggest($key, $val){
	/* Validate input */
	if( $key == 'custNumber' ){
		$key = 'number';
		if (!empty($val) && !is_numeric($val))
		{
			return say('Número de cliente inválido.');
		}
	}

	$customers = oSQL()->getCustomersForService( array($key => $val) );

	$data = array();
	foreach( $customers as $k => $customer ){
		$rows = oSQL()->getCustomerInstalls( $customer['id_customer'] );
		$data[$k] = array(
			'id_customer'	=> $customer['id_customer'],
			'customer'		=> "{$customer['number']} ({$customer['customer']})",
			'contact'		=> $customer['contact'],
			'rows'			=> $rows,
		);
	}

	return addScript("\$('#technicalForm').get(0).handler.suggest(".toJson($data).');');

}

function tchFormAcceptSale($id, $cust=NULL){	/* `id_sale` from `sales`, `id_customer` from `customers` */

	if( empty($id) && empty($cust) ) return say('Faltan datos requeridos.');

	if( empty($id) ){
		$data = oSQL()->getCustomer( $cust );
		$data['id_sale'] = $data['system'] = '';
		$data['warranty'] = 0;
		$data['address'] = "{$data['address']} ({$data['location']})";
		list($data['installDay'], $data['installMonth'], $data['installYear']) = array('--', '--', '--');
	}
	else{
		# Get all relevant info for this invoice (either install or sale)
		$data = oSQL()->getInstallForNewService( $id );
		if( empty($data) ) return say('Ocurrió un error al intentar leer los datos de la factura.');
		# Part install date and fix warranty (from warranty months to warranty void date)
		$data['warranty'] = strtotime($data['date'].' + '.$data['warranty'].' months') > time() ? 1 : 0;
		list($data['installYear'], $data['installMonth'], $data['installDay']) = explode('-', $data['date']);
	}

	# A few alias to match ids in the template (used by JS script)
	$data['onSale'] = $data['id_sale'];
	$data['custNumber'] = $data['number'];

	# Remove unused keys
	$toKeep = array('onSale', 'id_customer', 'customer', 'contact', 'address', 'phone',
		'custNumber', 'warranty', 'subscribed', 'id_system', 'system', 'installDay', 'installMonth', 'installYear');
	foreach( $data as $k => $v ) if( !in_array($k, $toKeep) ) unset( $data[$k] );

	return addScript("\$('#technicalForm').get(0).handler.fillForm(".toJson($data).');');

}

/**
 * Create/edit a technical visit (store it in the DB).
 */
function createTechVisit($data)
{
	# If it has id_sale, we're editing. It's a new record otherwise
	$sqlAxn = !empty($data['id_sale']) ? 'update' : 'insert';

	# Build visit date and starts/ends from its parts
	$data['date'] = format_date($data['year'], $data['month'], $data['day']);
	$data['starts'] = format_time($data['startsH'], $data['startsM']) or $data['starts'] = '';
	$data['ends'] = format_time($data['endsH'], $data['endsM']) or $data['ends'] = '';

	# We should have either an id_customer or an id_sale left
	if( empty($data['id_sale']) && empty($data['id_customer']) ){
		return say('Debe seleccionar un cliente (con o sin factura previa).');
	}
	if( empty($data['date']) ) return say('La fecha de la visita es un dato requerido.');

	# Cost has to be fixed and the appropriate currency chosen
	$data['currency'] = !empty($data['cost']) ? '$' : (!empty($data['costDollars']) ? 'U$S' : '');
	if( empty($data['cost']) ) $data['cost'] = $data['costDollars'];

	# Keys that should not be saved at all
	$keysToUnset = array('submit', 'day', 'month', 'year', 'startsH', 'startsM', 'endsH', 'endsM',
		'costDollars', 'customer', 'address', 'phone', 'custNumber', 'subscribed', 'warranty');
	foreach( $data as $k => $v ) if(in_array($k, $keysToUnset) || $v === '') unset( $data[$k] );

	# Validate input
	$res = oValidate()->test($data, 'techVisits');

	if ($res !== true)
	{
		addScript("\$('#technicalForm').handler.select('{$res['field']}');");
		$tip = strtolower($res['tip'] ? $res['tip'] : '');
		return say("Hay un error en los datos ingresados: {$tip}");
	}

	# Some fields are saved in `sales` table...
	$forSales = array('type' => 'service');

	if (!empty($data['id_sale']))
	{
		$forSales['id_sale'] = $data['id_sale'];
	}

	$keysSales = array('id_customer', 'id_system', 'invoice', 'date', 'currency', 'cost', 'contact');

	foreach ($keysSales as $key)
	{
		if (isset($data[$key]) && $data[$key] !== '')
		{
			$forSales[$key] = $data[$key];
		}

		unset($data[$key]);
	}

	# ...while the other fields go into `sales_services`
	foreach ($data as $k => $v)
	{
		if ($v !== '')
		{
			$forSvcs[$k] = $v;
		}
	}

	# Save input and handle errors
	oSQL()->BEGIN();
	$ans = oSQL()->$sqlAxn($forSales, 'sales', 'id_sale');

	if ($sqlAxn == 'insert')
	{
		$data['id_sale'] = $ans->ID;
	}

	if (!$ans->error && !empty($forSvcs))
	{
		$forSvcs['id_sale'] = $data['id_sale'];
		$ans = oSQL()->$sqlAxn($forSvcs, 'sales_services', 'id_sale');
	}

	if ($ans->error)
	{
		oSQL()->ROLLBACK();
		# Handle expected errors
		if ($ans->column == 'invoice' && $ans->error == SQL_ERROR_DUPLICATE)
		{
			return say("Ya existe una factura con número {$data['invoice']} registrada.");
		}

		if ($ans->column == 'number' && $ans->error = SQL_ERROR_DUPLICATE)
		{
			return say("El número de visita {$data['number']} ya está en uso.");
		}

		# Fall back to a generic error message
		return say('Ocurrió un error al intentar guardar el formulario. '.
				   'Revise sus datos e inténtelo nuevamente.');
	}

	oSQL()->COMMIT();

	# Success
	$axn = ($sqlAxn == 'insert') ? 'creada.' : 'modificada.';
	$msg = sprintf('La visita técnica fue correctamente %s.', $axn);

	return oNav()->getPage('techVisitsInfo', array($data['id_sale']), $msg, 1);
}