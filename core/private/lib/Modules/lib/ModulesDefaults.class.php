<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */
 
/**
 * This class inherits the following attributes from its parent, ModulesDefaults:
 *		protected $type
 *		protected $code
 *		protected $modifier
 * They are set from the start and might be used by any method in this class.
 * 
 * In turn, ModulesDefaults inherits Connection methods: query, asList, asHash,
 * modify, insert, update, delete, etc. (see class Connection for more info).
 */

	
	class ModulesDefaults extends Connection{
	
		public function __construct($type, $code, $modifier){
		
			parent::__construct();
			
			# Initialize
			$this->data = NULL;
		
			# Main parameters for this module
			$this->type = $type;
			$this->code = $code;
			$this->modifier = $modifier;
		
		}
		
		/**
		 * @overview: The name that will be shown to users for this object
		 */
		public function getName(){
		
			return '';
			
		}
		
		/**
		 * @overview: The name that will be shown to users for this object,
		 * when plural
		 */
		public function getPlural(){
		
			return '';
			
		}
		
		
		
		/**
		 * @overview: The list of attributes for each field that could be used by Modules.
		 */
		public function getFields(){
		
			$fields = array();
			
			$keys = (array)$this->getKeys();
			
			foreach( $keys as $key ){
				$fields[$key] = array(
					'name'		=> 'Key #'.(string)(count($fields) + 1),
					'class'		=> 'Mod_Lists_missing_fields',
					'hidden'	=> false,
				);
			}
			
			return $fields;
			
		}
		
		/**
		 * @overview: Returns the key(s) that identify each item, named by field code
		 */
		public function getKeys(){
		
			return array();
			
		}
		
		/**
		 * @overview: The code of the field that will be shown as a tooltip for the
		 * row, in lists.
		 */
		public function getTipField(){
		
			return '';
			
		}
		
		
		
		/**
		 * @overview: list of fields to be shown in commonList
		 */
		public function getCommonListFields(){
			
			# By default, if commonList fields were not defined, we check
			# whether simpleList defined fields.
			$simpleListFields = $this->getSimpleListFields();
			
			# If no list is defined, we do present commonList, and we take
			# all defined fields for it (except keys, that will be hidden)
			return  empty($simpleListFields)
				? array_keys( $this->getFields() )
				: NULL;
					
		}
		/* alias to getCommonListFields, needed when updating list */
		public function getInnerCommonListFields(){
			return $this->getCommonListFields();
		}
		
		/**
		 * @overview: list of fields to be shown in simpleList
		 */
		public function getSimpleListFields(){
		
			return NULL;
			
		}
		
		/**
		 * @overview: field to be shown in comboList as text
		 */
		public function getComboListField(){
		
			return 'comboField';
			
		}
		
		/**
		 * @overview: list of fields to be shown in infoPage
		 */
		public function getInfoFields(){
		
			return array_keys( $this->getFields() );
		
		}
		
		/**
		 * @overview: list of fields to be shown in createPage
		 */
		public function getCreateFields(){
		
			return $this->getInfoFields();
			
		}
		
		/**
		 * @overview: list of fields to be shown in editPage
		 */
		public function getEditFields(){
		
			return $this->getCreateFields();
			
		}
		
		
		
		/**
		 * @overview: get data for a commonList
		 */
		public function getCommonListData(){
			
			return NULL;
			
		}
		
		/**
		 * @overview: get data for a simpleList
		 */
		public function getSimpleListData( $filters=array() ){
		
			return NULL;
					
		}
		
		/**
		 * @overview: get data for a comboList
		 */
		public function getComboListData( $filters=array() ){
		
			return NULL;
			
		}
		
		public function getInfoPageData( $filters=array() ){
		
			return NULL;
		
		}
		
		
		
		/**
		 * @overview: a list of tools taken from available ones:
		 */
		public function getListTools(){
		
			return array();
			
		}
		
		
		
		/**
		 * @overview: this function will receive the filters for lists, before
		 * calling #getCommonListData (or it's ancestors if it's missing), by reference.
		 */
		public function checkFilter( &$filters ){
		}
		
		/**
		 * @overview: this function will receive the data retrieved by #getCommonListData
		 * (or it's ancestors if it's missing), by reference.
		 */
		public function checkData( &$data ){
		}
		
		
		
		/**
		 * @overview: if createPage or editPage are available, it is a good idea to
		 * set validation rules for each field. See Rules class for available rules,
		 * or create your own regular expressions to validate from (just add a slash
		 * before and after the expression).
		 */
		public function validationRuleSet(){
		
			return array();
			
		}
		 
		/**
		 * @overview: Validates not only received fields but also that all fields
		 * defined in the ruleset are actually received.
		 */
		public function strictValidation(){
		
			return false;
			
		}
		
	}

?>