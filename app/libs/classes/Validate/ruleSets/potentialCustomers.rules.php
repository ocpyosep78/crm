<?php
	
	return array(
		'number'		=> array('text', NULL, 10),
		'customer'		=> array('text', 2, 50),
		'legal_name'	=> array('text', NULL, 40),
		'rut'			=> array('num', NULL, 12),
		'phone'			=> array('phone', NULL, 20 ),
		'email'			=> array('email', NULL, 50),
		'address'		=> array('text', NULL, 50),
		'id_location'	=> array('selection'),
	);