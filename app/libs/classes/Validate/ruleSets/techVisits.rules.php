<?php

	return array(
		'id_sale'			=> array('num', 0, 9),
		'invoice'			=> array('alpha', 0, 7),
		'date'				=> array('date', 1),
		'currency'			=> array('/^(\$|U\$S)$/'),
		'cost'				=> array('cost'),
		'contact'			=> array('text', NULL, 80),
		/*---------------------------------------------------*/
		'onSale'			=> array('num', 0, 9),
		'number'			=> array('num', 0, 7),
		'starts'			=> array('time'),
		'ends'				=> array('time'),
		'reason'			=> array('text', 0, 120),
		'outcome'			=> array('text', 0, 120),
//		'quality'			=> array('/^(bad|regular|good|excellent)$/'),
		'order'				=> array('alpha', NULL, NULL),
		'complete'			=> array('bool'),
		'ifIncomplete'		=> array('text', 0, 120),
		'usedProducts'		=> array('text', 0, 120),
		'pendingEstimate'	=> array('bool'),
	);
	
?>