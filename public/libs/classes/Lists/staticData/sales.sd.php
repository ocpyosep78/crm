<?php

	$params = array(
		'name'		=> 'Venta',
		'plural'	=> 'Ventas',
		'tipField'	=> 'description',
	);
		
	$fields = array(
		'type'			=> 'Tipo',
		'date'			=> 'Fecha',
		'invoice'		=> 'Nº Factura',
		'customer'		=> 'Cliente',
		'notes'			=> 'Descripción / Notas',
//		'id_sale'		=> '',
//		'id_customer'	=> '',
//		'technician'	=> '',
	);
	
	$tools = array(
		'edit',
		'delete',
	);
	
	$preProcess = 'fixSalesFilter';
	$postProcess = 'fixSalesList';