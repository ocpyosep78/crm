<?php

	$params = array(
		'name'		=> 'Venta',
		'plural'	=> 'Ventas',
		'tipField'	=> 'description',
	);
		
	$fields = array(
		'type'			=> 'Tipo',
		'date'			=> 'Fecha',
		'invoice'		=> 'N� Factura',
		'customer'		=> 'Cliente',
		'notes'			=> 'Descripci�n / Notas',
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