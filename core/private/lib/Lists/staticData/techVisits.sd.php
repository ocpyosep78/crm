<?php

	$params = array(
		'name'		=> 'Visita T�cnica',
		'plural'	=> 'Visitas T�cnicas'
	);

	$fields = array(
		'date'			=> 'Fecha',
		'number'		=> 'N�mero',
		'customer'		=> 'Cliente',
#		'period'		=> 'Desde/hasta',
		'technician'	=> 'T�cnico',
		'quality'		=> 'Calificaci�n',
#		'saleInvoice'	=> 'Factura (venta)',
		'invoice'		=> 'Factura (visita)',
#		'attachedOrder'	=> 'Orden Adjunta',
	);
	
	$tools = array(
		'edit',
		'delete',
	);
	
	$postProcess = 'fixTechVisitsList';

?>