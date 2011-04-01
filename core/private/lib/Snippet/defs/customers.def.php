<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	
	class Snippet_def_customers extends Snippets_Handler_Interpreter{
	
		public function getBasicAttributes(){
			
			return array(
				'name'		=> 'Cliente',
				'plural'	=> 'Clientes',
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
		public function getDatabaseDefinition(){
		
			return array(
				'customers' => array(
					'id_customer'	=> array('name' => 'ID', 'isKey' => true),
					'number'		=> 'Número',
					'customer'		=> 'Empresa',
					'legal_name'	=> 'Razón Social',
					'rut'			=> 'RUT',
					'address'		=> 'Dirección',
					'id_location'	=> '',
					'phone'			=> 'Teléfono',
					'email'			=> 'Email',
					'seller'		=> array('FK' => '_users.user', 'hidden' => true),
					'since'			=> 'Fecha Ingreso',
					'subscribed'	=> array('name' => 'Subscripción', 'hidden' => true),
				),
				'_locations' => array(
					'id_location'	=> array('FK' => 'customers.id_location', 'hidden' => true),
					'location'		=> 'Ciudad/Localidad',
				),
				'_users' => array(
					'user'		=> '',
					'seller'	=> array('name' =>'Vendedor', 'aliasOf' => '$name $lastName'),
				),
			);
			
		}
		
		
		public function getListFields(){
		
			return array('number', 'customer', 'legal_name', 'address', 'phone', 'sellerName');
			
		}
		
		public function getItemFields(){
		
			return array('number', 'customer', 'legal_name', 'rut', 'since', '>',
				'phone', 'email', 'address', 'location', 'sellerName');
			
		}
		
		public function getListData( $filters=array() ){
			$this->fixFilters(&$filters, array(
				'phone'			=> '`c`.`phone`',
				'address'		=> '`c`.`address`',
				'sellerName'	=> "CONCAT(`u`.`name`,' ',`u`.`lastName`)",
			));
			return "SELECT	`c`.*,
							CONCAT(`u`.`name`,' ',`u`.`lastName`) AS 'sellerName',
							`lc`.*,
							CONCAT(`c`.`customer`,
								' (', `c`.`legal_name`, ') ',
								'(Cliente ', `c`.`number`, ')') AS 'tipToolText'
					FROM `customers` `c`
					LEFT JOIN `_users` `u` ON (`u`.`user` = `c`.`seller`)
					LEFT JOIN `_locations` `lc` USING (`id_location`)
					WHERE {$this->array2filter($filters)}
					AND {$this->getFilterFromModifier()}
					ORDER BY `c`.`customer`";
		}
public function getItemData( $id ){
	return $this->getListData( array('id_customer' => $id) );
}
private function getFilterFromModifier(){
	switch( $this->params['modifier'] ){
		case 'customers': return 'NOT ISNULL(`since`)';
		case 'potential': return 'ISNULL(`since`)';
	}
	return '1';		# No filter for status (show all customers)
}
		public function getComboListData( $filters=array() ){
			return "SELECT	`id_customer`,
							`customer`
					FROM `customers`
					WHERE {$this->getFilterFromModifier()}
					ORDER BY `customer`";
		}
		public function getTools(){
			return array('view', 'create', 'edit', 'delete');
		}
		
/*		public function checkFilter( &$filters ){
		}/**/
		
/*		public function checkData( &$data ){
		}/**/
		
/*		public function validationRuleSet(){
		}/**/
		
/*		public function strictValidation(){
			return true;
		}/**/
		
	}

?>