<?php

	return array(
		'code'			=> array('text', 1, 20),
		'trademark'		=> array('text', 1, 50),
		'model'			=> array('text', NULL, 50),
		'provider'		=> array('text', NULL, 120),
		'warranty'		=> array('selection'),
		'id_system'		=> array('selection'),
	);