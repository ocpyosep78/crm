<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	
	class Snippet_def_calls extends Snippets_Handler_Source{
	
		protected function getBasicAttributes(){
			
			return array(
				'name'		=> 'Llamada',
				'plural'	=> 'Llamadas',
				'gender'	=> 'f',
			);
			
		}
		
		
		/**
		 * @overview: List of tables involving this module, and each relevant field in them,
		 *            plus attributes for each field that could be used by Modules.
		 *            Attributes might have the following fields:
         *                name     Screen name for this field
         *                type     text, image, area, combo (defaults to text)
         *                isKey    whether this field is a key
         *                hidden   whether to hide this field in infoPage
         *                FK       i.e. ['sales']['seller']['FK'] = 'users.user'
		 *            Field will be ignored for output when name is empty or hidden is true
		 *            Fields flagged as keys (isKey => true) will be hidden by default. Set
		 *            hidden => false explicitly to override.
		 *            By default, item pages will use all fields that are not hidden. To
		 *            override this behavior, use getItemFields.
		 */
		protected function getDatabaseDefinition(){
		
			return array(
				'_calls' => array(
					'id_call'		=> array('name' => 'ID', 'isKey' => true),
					'caller'		=> 'Quién llama',
					'date'			=> array('name' => 'Fecha / Hora', 'type' => 'datetime'),
					'detail'		=> array('name' => 'Motivo / Descripción', 'type' => 'area'),
					'id_note'		=> array('name' => 'Nota asociada', 'FK' => '_notes.id_note'),
				),
				'customers' => array(
					'customer'		=> array('name' => 'Cliente asociado'),
					'id_customer'	=> array('name' => 'Cliente asociado', 'type' => 'list', 'listSrc' => 'customers'),
				),
				'_users' => array(
					'user'		=> array('name' =>'Usuario asignado', 'type' => 'list'),
					'assigned'	=> array('name' =>'Usuario asignado', 'aliasOf' => '$name $lastName'),
				),
			);
			
		}
		
		
		protected function getListFields(){
		
			return array('date', 'detail', 'caller', 'customer', 'assigned');
			
		}
		
		protected function getCreateFields(){
		
			return array('date', 'detail', 'caller', 'id_customer', 'user');
			
		}
		
		protected function getItemFields(){
		
			return array('date', 'detail', 'caller', 'id_customer', 'user');
			
		}
		
		protected function getTools(){
			return array('view', 'create', 'edit', 'delete');
		}
				
/*		protected function checkFilter( &$filters ){
		}/**/
		
/*		protected function checkData( &$data ){
		}/**/
		
		protected function getValidationRuleSet(){

			return array();/*
				'number'		=> array('text', NULL, 10),
				'customer'		=> array('text', 2, 80),
				'legal_name'	=> array('text', 2, 80),
				'rut'			=> array('rut', NULL, 12),
				'phone'			=> array('phone', 3, 20 ),
				'email'			=> array('email', NULL, 50),
				'address'		=> array('text', NULL, 50),
				'id_location'	=> array('selection'),
			);*/
			
		}/**/
		
/*		protected function strictValidation(){
			return true;
		}/**/

/* TEMP : All these methods below should be automatically created based on the definition */
		
private function globalFilters( &$filters ){

	$srch = $filters['*'];
	$filters = array();
	$fields = array_diff($this->getItemFields(), (array)'>');
	
	foreach( $fields as $field ) $filters["`{$field}`"] = $srch;
	
}
		
protected function getListData( $filters=array() ){
	$sql = "SELECT	`c`.`customer`,
					CONCAT(`u`.`name`,' ', `u`.`lastName`) AS 'assigned',
					`ll`.*
			FROM `_calls` `ll`
			LEFT JOIN `_users` `u` USING (`user`)
			LEFT JOIN `customers` `c` USING (`id_customer`)
			WHERE {$this->array2filter($filters)}
			ORDER BY `ll`.`date`";
	return $sql;
}
protected function getItemData( $id ){
	return $this->getListData( array('id_customer' => array($id, '=')) );
}
private function getFilterFromModifier(){
	switch( $this->params['modifier'] ){
		case 'customers': return 'NOT ISNULL(`since`)';
		case 'potential': return 'ISNULL(`since`)';
	}
	return '1';		# No filter for status (show all customers)
}
protected function getComboListData( $filters=array() ){
	return "SELECT	`id_customer`,
					`customer`
			FROM `customers`
			WHERE {$this->getFilterFromModifier()}
			ORDER BY `customer`";
}

		protected function listForFieldUser(){
			return "SELECT	`user`,
							CONCAT(`name`, ' ', `lastName`)
					FROM `_users`";
		}
		
	}

?>