<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	
	class Snippet_def_customers extends Snippets_Handler_Source{
	
		protected function getBasicAttributes(){
			
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
		 */
		protected function getDatabaseDefinition(){
		
			return array(
				'customers' => array(
					'id_customer'	=> array('name' => 'ID', 'isKey' => true),
					'number'		=> 'Número',
					'customer'		=> 'Empresa',
					'legal_name'	=> 'Razón Social',
					'rut'			=> 'RUT',
					'address'		=> 'Dirección',
					'billingaddress'=> 'Dir. de Facturación',
					'id_location'	=> '',
					'phone'			=> 'Teléfono',
					'email'			=> 'Email',
					'seller'		=> array('FK' => '_users.user', 'hidden' => true),
					'since'			=> array('name' => 'Fecha Ingreso', 'frozen' => true),
					'subscribed'	=> array('name' => 'Subscripción', 'hidden' => true),
				),
				'_locations' => array(
					'id_location'	=> array('FK' => 'customers.id_location', 'hidden' => true),
					'location'		=> array('name' => 'Ciudad/Localidad', 'frozen' => true),
				),
				'_users' => array(
					'user'		=> '',
					'seller'	=> array('name' =>'Vendedor', 'aliasOf' => '$name $lastName'),
				),
			);
			
		}
		
		protected function getFieldsFor( $type ){
		
			switch( $type ){
				case 'list':
					return array('number', 'customer', 'legal_name', 'address', 'phone', 'sellerName');
				case 'view':
					return array('number', 'customer', 'legal_name', 'rut', 'since', '>',
						'phone', 'email', 'address', 'billingaddress', 'location', 'sellerName');
				case 'create':
				case 'edit':
					return array('number', 'customer', 'legal_name', 'rut', 'since', '>',
						'phone', 'email', 'address', 'location', 'sellerName');
			}
		
		}
		
		protected function getTools(){
            SnippetLayer_access::addCustomPermit('newCustomerTech');
            SnippetLayer_access::addCustomPermit('newAgendaEvent');
		
			return array('view',
                         array('newCustomerTech' => 'Nueva ficha técnica para el'),
                         array('newAgendaEvent'  => 'Nuevo evento para el'),
                         'create',
                         'edit',
                         'delete');
		}
				
/*		protected function checkFilter( &$filters ){
		}/**/
		
/*		protected function checkData( &$data ){
		}/**/
		
		protected function getValidationRuleSet(){

			return array(
				'number'		=> array('text', NULL, 10),
				'customer'		=> array('text', 2, 80),
				'legal_name'	=> array('text', 2, 80),
				'rut'			=> array('rut', NULL, 12),
				'phone'			=> array('phone', 3, 40 ),
				'email'			=> array('email', NULL, 50),
				'address'		=> array('text', NULL, 80),
				'billingaddress'=> array('text', NULL, 80),
				'id_location'	=> array('selection'),
			);
			
		}/**/
		
/*		protected function strictValidation(){
			return true;
		}/**/

/* TEMP : All these methods below should be automatically created based on the definition */
		
private function globalFilters( &$filters ){

	$srch = $filters['*'];
	$filters = array();
	
	$fields = array_diff($this->getFieldsFor('view'), (array)'>');
	foreach( $fields as $field ) $filters["`{$field}`"] = $srch;
	
	$filters["`cc`.`name`"] = $srch;
	$filters["`co`.`name`"] = $srch;
	
}
		
protected function getListData($filters=array(), $join='AND'){
	if( isset($filters['*']) ){
		$this->globalFilters( $filters );
		$join = 'OR';
	}
	$this->fixFilters($filters, array(
		'phone'			=> '`c`.`phone`',
		'address'		=> '`c`.`address`',
		'email'			=> '`c`.`email`',
		'sellerName'	=> "CONCAT(`u`.`name`,' ',`u`.`lastName`)",
	));
	$sql = "SELECT	`c`.*,
					DATE_FORMAT(`c`.`since`, '%d-%m-%Y') AS 'since',
					CONCAT(`u`.`name`,' ',`u`.`lastName`) AS 'sellerName',
					`lc`.*,
					CONCAT(`c`.`customer`,
						IF(`c`.`legal_name` = '', '', CONCAT(' (', `c`.`legal_name`, ')')),
						' (Cliente ', IFNULL(`c`.`number`, 'sin número'), ')') AS 'tipToolText'
			FROM `customers` `c`
			LEFT JOIN `_users` `u` ON (`u`.`user` = `c`.`seller`)
			LEFT JOIN `_locations` `lc` USING (`id_location`)
			LEFT JOIN `customers_contacts` `cc` USING (`id_customer`)
			LEFT JOIN `customers_owners` `co` USING (`id_customer`)
			WHERE ({$this->array2filter($filters, $join)})
			AND {$this->getFilterFromModifier()}
			ORDER BY `c`.`customer`";
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
		
	}