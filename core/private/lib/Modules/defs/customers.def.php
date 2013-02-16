<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	
	class Modules_Definitions_customers extends Modules_Definitions{
	
		# name, plural and gender
		protected function basics(){
			
			$this->name	= 'Cliente';
			$this->plural = 'Clientes';
			$this->gender = 'male';
			
		}
		
		protected function longIdentifier(){
			
			$this->longName = '{$customer} ({$legal_name}) (Cliente {$number})';
			
		}
		
		protected function dbStructure(){
		
			$this->tables['customers'] = array(
				'number'		=> 'Número',
				'customer'		=> 'Empresa',
				'legal_name'	=> 'Razón Social',
				'rut'			=> 'RUT',
				'address'		=> 'Dirección',
				'phone'			=> 'Teléfono',
				'email'			=> 'Email',
				'since'			=> 'Fecha Ingreso',
//				'subscribed'	=> 'Subscripción',
			);
			$this->tables['customers_contacts'] = array(
				'name'			=> 'Nombre',
				'phone'			=> 'Teléfono',
				'email'			=> 'Email',
			);
			$this->tables['customers_owners'] = array(
				'name'			=> 'Nombre',
				'docNum'		=> 'Documento',
				'phone'			=> 'Teléfono',
				'email'			=> 'Email',
				'address'		=> 'Dirección',
			);
			$this->tables['_locations'] = array(
				'location'		=> 'Ciudad/Localidad',
			);
			$this->tables['_users'] = array(
				'user'			=> '',
				'seller'		=> 'Vendedor',
			);
			
		}
		
		protected function dbRules(){
			
			$this->rules['customers'] = array(
				'keys'		=> 'id_customer',
			);
			$this->rules['customers_contacts'] = array(
				'prefix'	=> 'cc',
				'join'		=> 'id_customer',
			);
			$this->rules['customers_owners'] = array(
				'prefix'	=> 'co',
				'join'		=> 'id_customer',
			);
			$this->rules['_locations'] = array(
				'prefix'	=> 'l',
				'join'		=> 'id_location',
			);
			$this->rules['_users'] = array(
				'prefix'	=> 'u',
				'join'		=> array('user' => 'seller'),
				'aliases'	=> array('seller' => '{$name} {$lastName}'),
			);
			
		}
		# Fields to be included in list queries
		protected function listColumns(){
		
			$this->listFields = array('number', 'customer',
				'legal_name', 'address', 'phone', 'sellerName');
			
		}
		
		# Fields to be included in item queries
		protected function itemRows(){
		
			$this->itemFields = array('number', 'customer',
				'legal_name', 'rut', 'since', '>', 'phone', 'email',
				'address', 'location', 'sellerName');
			
		}
		
		# Fields requested at creation and validation rules
		protected function createItem(){
			
			$this->creationFields = array('number', 'customer',
				'legal_name', 'rut', 'phone', 'email', 'address');
			
		}
		
		# Called after building automatic filters, before query
		protected function fixFilters(){
			
			switch( $this->category ){
				case 'customers': return 'NOT ISNULL(`since`)';
				case 'potential': return 'ISNULL(`since`)';
			}
			return '1';		# No filter for status (show all customers)
			
		}
		
		# Called after query, before returning results
		protected function fixData(){
		}
		
		# Validation rules set
		protected function validation(){
			
			$this->ruleSet = array(
				'id_location'	=> array('selection'),
				'number'		=> array('text', NULL, 10),
				'customer'		=> array('text', 2, 80),
				'legal_name'	=> array('text', 2, 80),
				'rut'			=> array('rut', NULL, 12),
				'phone'			=> array('phone', 3, 40 ),
				'email'			=> array('email', NULL, 50),
				'address'		=> array('text', NULL, 50),
			);
			
		}
		
	}