<?php

	$params = array(
		'name'		=> $modifier == 'isQuote' ? 'Cotización' : 'Presupuesto',
		'plural'	=> $modifier == 'isQuote' ? 'Cotizaciones' : 'Presupuestos',
	);
	
	$fields = array(
//		'id_estimate'	=> '',
		'orderNumber'	=> 'Nº Orden',
		'estimate'		=> 'Nombre',
//		'id_customer'	=> '',
		'customer'		=> 'Cliente',
//		'id_system'		=> '',
		'system'		=> 'Sistema',
		'estimateDate'	=> 'Fecha',
	);