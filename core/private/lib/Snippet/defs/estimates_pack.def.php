<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	
	class Snippet_def_estimates_pack extends Snippets_Handler_Source{
	
		protected function getBasicAttributes(){
			
			return array(
				'name'		=> 'Presupuesto Corporativo',
				'plural'	=> 'Presupuestos Corporativos',
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
		
			$data = array(
				'estimates_pack' => array(
					'id_estimates_pack'	=> array('isKey' => true),
					'name'				=> 'Nombre',
					'created'			=> 'Fecha',
					'sellerName'		=> 'Vendedor',
					'estimates'			=> array('name' => 'Presupuestos'),
				),
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
					'since'			=> array('name' => 'Fecha Ingreso'),
					'subscribed'	=> array('name' => 'Subscripción', 'hidden' => true),
				),
				'_users' => array(
					'user'		=> '',
					'seller'	=> array('name' =>'Vendedor', 'aliasOf' => '$name $lastName'),
				),
			);
			foreach( $data as $k => &$v ) foreach( $v as $field => &$atts ){
				if( !is_array($atts) ) $atts = array('name' => $atts);
				$atts['frozen'] = true;
			}
			
			return $data;
			
		}
		
		protected function getFieldsFor( $type ){
		
			switch( $type ){
				case 'list':
					return array('created', 'name', 'customer', 'sellerName', 'estimates');
				case 'view':
					return array('created', 'name', 'customer', 'sellerName', 'estimates', '>',
						'number', 'customer', 'legal_name', 'rut', 'phone', 'email', 'address');
				case 'create':
				case 'edit':
					return array('number', 'customer', 'legal_name', 'rut', 'since', '>',
						'phone', 'email', 'address', 'location', 'sellerName');
			}
		
		}
		
		protected function getItemData( $id ){
		
			# Get main data for this estimates pack
			$sql = "SELECT	`ep`.*,
							DATE_FORMAT(`ep`.`created`, '%d-%m-%Y') AS 'created',
							`c`.`number`,
							`c`.`customer`,
							`c`.`legal_name`,
							`c`.`rut`,
							`c`.`phone`,
							`c`.`email`,
							`c`.`address`,
							CONCAT(`u`.`name`,' ',`u`.`lastName`) AS 'sellerName'
					FROM `estimates_pack` `ep`
					LEFT JOIN `customers` `c` ON (`c`.`id_customer` = `ep`.`id_customer`)
					LEFT JOIN `_users` `u` ON (`u`.`user` = `c`.`seller`)
					WHERE `ep`.`id_estimates_pack` = '{$id}'
					ORDER BY `ep`.`created`";
			$data = $this->sqlEngine->query($sql, 'row');
			
			# Attach links to each member
			$sql = "SELECT	`e`.`id_estimate`,
							`e`.`estimate`
					FROM `estimates` `e`
					WHERE `pack` = '{$id}'
					ORDER BY `e`.`estimate`";
			$members = $this->sqlEngine->query($sql, 'col');
			foreach( $members as $k => $v ){
				$arr[] = "<a href='javascript:void(0);' onclick=\"getPage('estimatesInfo', ['{$k}'])\">{$v}</a>";
			}
			$data['estimates'] = isset($arr) ? join('<br />', $arr) : '(ninguno)';
			
			return $data;
			
		}
		
		protected function getTools(){
		
			return array('view', 'create', 'edit', 'delete');
			
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
				'phone'			=> array('phone', 3, 20 ),
				'email'			=> array('email', NULL, 50),
				'address'		=> array('text', NULL, 50),
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
	
}
		
protected function getListData($filters=array(), $join='AND'){
	if( isset($filters['*']) ){
		$this->globalFilters( $filters );
		$join = 'OR';
	}
	$this->fixFilters(&$filters, array(
		'sellerName'	=> "CONCAT(`u`.`name`,' ',`u`.`lastName`)",
	));
	$sql = "SELECT	`ep`.*,
					`c`.*,
					DATE_FORMAT(`ep`.`created`, '%d-%m-%Y') AS 'created',
					CONCAT(`u`.`name`,' ',`u`.`lastName`) AS 'sellerName',
					COUNT(`e`.`pack`) AS 'estimates'
			FROM `estimates_pack` `ep`
			LEFT JOIN `estimates` `e` ON (`e`.`pack` = `ep`.`id_estimates_pack`)
			LEFT JOIN `customers` `c` ON (`c`.`id_customer` = `ep`.`id_customer`)
			LEFT JOIN `_users` `u` ON (`u`.`user` = `c`.`seller`)
			WHERE ({$this->array2filter($filters, $join)})
			GROUP BY `ep`.`id_estimates_pack`
			ORDER BY `ep`.`created`";
	return $sql;
}
		
	}

?>