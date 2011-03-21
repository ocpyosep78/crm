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
		'category'		=> $modifier == 'products' ? 'Categoría' : NULL,
		'code'			=> $modifier == 'products' ? 'Código' : NULL,
		'name'			=> 'Nombre',
		'model'			=> $modifier == 'products' ? 'Modelo' : NULL,
		'trademark'		=> $modifier == 'products' ? 'Marca' : NULL,
//		'price'			=> 'Precio',
		'description'	=> $modifier != 'products' ? 'Descripción' : NULL,
//		'system'		=> 'Sistema',
	);
	
	if( $modifier == 'products' ){
//		$fields['code'] = 'Código',
	}

?>