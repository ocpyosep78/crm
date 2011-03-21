<?php
	
	return array(
		'user'			=> array('alpha', 2, 20),
		'pass'			=> array('alphaMixed', 4, 12),
		'id_profile'	=> array('selection'),
		'name'			=> array('text', 2, 40 ),
		'lastName'		=> array('text', 2, 40 ),
		'phone'			=> array('phone', 3, 20 ),
		'address'		=> array('text', 2, 40 ),
		'email'			=> array('email', 2, 40 ),
		'id_department'	=> array('selection'),
		'position'		=> array('text', 0, 40 ),
		'employeeNum'	=> array('alpha', 0, 20 ),
	);
	
?>