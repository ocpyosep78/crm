<?php

	$names = array(
		'products'	=> array('Producto', 'Productos'),
		'materials'	=> array('Material', 'Materiales'),
		'services'	=> array('Servicio', 'Servicios'),
		'others'	=> array('Otro Producto', 'Otros Productos'),
	);

	$params = array(
		'name'		=> isset($names[$modifier]) ? $names[$modifier][0] : 'Producto',
		'plural'	=> isset($names[$modifier]) ? $names[$modifier][1] : 'Productos',
		'tip'		=> 'description',
	);

	$fields = array(
		'category'		=> $modifier == 'products' ? 'Categor�a' : NULL,
		'code'			=> $modifier == 'products' ? 'C�digo' : NULL,
		'name'			=> 'Nombre',
		'model'			=> $modifier == 'products' ? 'Modelo' : NULL,
		'trademark'		=> $modifier == 'products' ? 'Marca' : NULL,
//		'price'			=> 'Precio',
		'description'	=> $modifier != 'products' ? 'Descripci�n' : NULL,
//		'system'		=> 'Sistema',
	);
	
	if( $modifier == 'products' ){
//		$fields['code'] = 'C�digo',
	}