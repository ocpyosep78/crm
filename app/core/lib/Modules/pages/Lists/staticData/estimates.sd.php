<?php

	$params = array(
		'name'		=> $modifier == 'isQuote' ? 'Cotizaci�n' : 'Presupuesto',
		'plural'	=> $modifier == 'isQuote' ? 'Cotizaciones' : 'Presupuestos',
	);
	
	$fields = array(
//		'id_estimate'	=> '',
		'orderNumber'	=> 'N� Orden',
		'estimate'		=> 'Nombre',
//		'id_customer'	=> '',
		'customer'		=> 'Cliente',
//		'id_system'		=> '',
		'system'		=> 'Sistema',
		'estimateDate'	=> 'Fecha',
	);

?>