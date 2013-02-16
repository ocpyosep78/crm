<?php

	$params = array(
		'name'		=> 'Visita Técnica',
		'plural'	=> 'Visitas Técnicas'
	);

	$fields = array(
		'date'			=> 'Fecha',
		'number'		=> 'Número',
		'customer'		=> 'Cliente',
#		'period'		=> 'Desde/hasta',
		'technician'	=> 'Técnico',
		'quality'		=> 'Calificación',
#		'saleInvoice'	=> 'Factura (venta)',
		'invoice'		=> 'Factura (visita)',
#		'attachedOrder'	=> 'Orden Adjunta',
	);
	
	$tools = array(
		'edit',
		'delete',
	);
	
	$postProcess = 'fixTechVisitsList';