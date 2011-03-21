<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	function getSalesTypes(){
	
		return array(
			'install'	=> 'Instalación',
			'sale'		=> 'Venta',
			'service'	=> 'Visita Técnica',
		);
		
	}

	function fixSalesFilter( $filters ){
	
		if( empty($filters['type']) ) return;
	
		/* Get possible 'translated' types and build RegExp with the right wildcards  */
		$types = getSalesTypes();
		$typeRE = '/'.str_replace(array('\*', '%'), '.*', preg_quote($filters['type'])).'/i';
		
		# See if search term matches any of the screen names for types
		foreach( $types as $k => &$v ) if( preg_match($typeRE, $v) ) return $filters['type'] = $k;
		
		return $filters['type'] = '.';	/* Make sure no accidental match happens */
	
	}

	function fixSalesList( $data ){
		
		/* Get all 'translated' values for type */
		$types = getSalesTypes();
	
		foreach( $data as &$row ){
			if( empty($row['invoice']) ) $row['invoice'] = '(sin especificar)';
			if( isset($types[$row['type']]) ) $row['type'] = $types[$row['type']];
		}
	
	}
	
	function postProcessNotesData( $data ){
	
		foreach( $data as &$row ){
			$row['visibility'] = !empty($row['user']) ? 'Privado' : 'Público';
		}
	
	}

?>