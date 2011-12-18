<?php

	return array(
		'id_customer'	=> array('selection'),
		'invoice'		=> array('num', 4, 5),
		'date'			=> array('date', 1, NULL),
		'warranty'		=> array('selection'),
		'id_system'		=> array('selection'),
		'id_installer'	=> array('selection'),
		'technician'	=> array('selection'),
		'description'	=> array('text', NULL, 120),
	);