<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	
	class Snippet_def_activity_types extends Snippets_Handler_Source{
	
		protected function getBasicAttributes(){
			
			return array(
				'name'		=> 'Tipo',
				'plural'	=> 'Tipos',
				'gender'	=> 'm',
			);
			
		}
		protected function getDatabaseDefinition(){
		
			$tables = array(
				'types' => array(
					'type'		=> array('name' => 'Tipo', 'isKey' => true),
					'typeLabel'		=> 'Tipo',
				),
			);
			
			foreach( $tables as &$table ) foreach( $table as &$atts ) $atts['frozen'] = true;
			
			return $tables;
			
		}
				
		protected function getListData($filters=array(), $join='AND'){
		
			return array(
				'technical' => 'Técnica',
				'sales'		=> 'Ventas',
			);
			
		}
		
	}