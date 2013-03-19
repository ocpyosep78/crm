<?php

	return array(
		'number'      => array('text', NULL, 10),
		'customer'    => array('open', 2, 80),
		'legal_name'  => array('open', 2, 80),
		'rut'         => array('rut', NULL, 12),
		'phone'       => array('phone', 3, 40 ),
		'email'       => array('email', NULL, 50),
		'address'     => array('open', NULL, 50),
		'billingaddr' => array('open', NULL, 50),
		'id_location' => array('selection'),
	);