<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	function fixTechVisitsList( $data ){
	
		$list = array(
			'bad'		=> 'Mala',
			'regular'	=> 'Regular',
			'good'		=> 'Buena',
			'excellent'	=> 'Excelente',
		);
		
		foreach( $data as &$row ){
			$row['quality'] = ($qty=$row['quality']) ? $list[$qty] : '';
		}
		
	}

?>