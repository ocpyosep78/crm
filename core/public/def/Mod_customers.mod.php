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

	
	class Mod_customers extends ModulesDefaults{
		
		/**
		 * @overview: The name that will be shown to users for this object
		 * @returns: string
		 * @default: defaults to '' if commented out
		 */
		public function getName(){
		
			return gettext('Cliente');
			
		}/**/
		
		/**
		 * @overview: The name that will be shown to users for this object,
		 * when plural
		 * @returns: string
		 * @default: defaults to '' if commented out
		 */
		public function getPlural(){
	
			return gettext('Clientes');
			
		}/**/
		
		
		public function getTables(){
		
			return 'customers';
			
		}
		
		
		
		/**
		 * @overview: The list of attributes for each field that could be used by Modules.
		 * @returns: Array of code => attributes arrays. Attributes might have the following fields:
				name		Screen name for this field
				class		a class to add to this field, intended for CSS purposes
				type		From: text, image, area, combo (defaults to text)
							image will produce an image in infoPage, a file input in createPage and both in editPage
							for lists, images will be shown miniaturized in lists, and link to the image in _blank page
							combo will produce text in infoPage, a combo in both createPage and editPage
							for lists, combo will add a special search showing only possible values to choose from
				isKey		whether this field is a key
				hide		whether to hide this field in infoPage
		 * all attributes are optional, but field will be ignored in lists if name is empty
		 * fields flagged as keys (isKey === true) will be hidden by default. Ser hide === false explicitly to override
		 * by default, infoPage, createPage and editPage will use all fields that are not hidden
		 * to override this behaviour, use getCreateFields or getEditFields (infoPage cannot override it)
		 * if one of these is created and the other one isn't, both will point to the one that is defined
		 * @notes: See ModulesBase@fieldTypes for a list of possible field types
		 */
		public function getFields(){
		
			return array(
				'id_customer'	=> 'ID interno',
				'number'		=> 'Número',
				'customer'		=> 'Empresa',
				'legal_name'	=> 'Razón Social',
				'rut'			=> 'RUT',
				'address'		=> 'Dirección',
				'id_location'	=> 'ID Ciudad',
				'location'		=> 'Ciudad',
				'phone'			=> 'Teléfono',
				'email'			=> 'Email',
				'sellerName'	=> 'Vendedor',
				'since'			=> 'Fecha Ingreso',
			);
			
		}/**/
		
		/**
		 * @overview: Returns the key(s) that identify each item, named by
		 * field code
		 * @returns: string or array of strings
		 * @default: if commented out or empty, the following pages will be
		 *           unavailable: infoPage, editPage
		 */
		public function getKeys(){
		
			return 'id_customer';
			
		}/**/
		
		/**
		 * @overview: The code of the field that will be shown as a tooltip
		 * for each row in lists.
		 * @returns: string
		 * @default: if commented out, no tooltip will be shown
		 */
/*		public function getTipField(){
		}/**/
		
		
		
		/**
		 * @overview: list of fields to be shown in commonList
		 * @returns: a numeric array with field codes
		 * @default: if commented out, commonList won't be available
		 */
		public function getCommonListFields(){
		
			return array('number', 'customer', 'legal_name', 'address', 'phone', 'sellerName');
			
		}/**/
		
		/**
		 * @overview: list of fields to be shown in simpleList
		 * @returns: a numeric array with field codes
		 * @default: if commented out, simpleList won't be available
		 */
/*		public function getSimpleListFields(){
		}/**/
		
		/**
		 * @overview: field to be shown in comboList as text
		 * @returns: string
		 * @default: if commented out, comboList won't be available
		 */
		public function getComboListFields(){
			return 'customer';
		}/**/
		
		/**
		 * @overview: list of fields to be shown in infoPage
		 * @returns: a numeric array with field codes
		 * @default: if commented out, all defined (and not hidden) fields
		 *           will be used
		 * @tip: use '>' as field code to part information in blocks
		 */
		public function getInfoFields(){
		
			return array('number', 'customer', 'legal_name', 'rut', 'since', '>',
				'phone', 'email', 'address', 'location', 'sellerName');
			
		}/**/
		
		/**
		 * @overview: list of fields to be shown in createPage
		 * @returns: a numeric array with field codes
		 * @default: if commented out, all infoPage fields will be used
		 */
/*		public function getCreateFields(){
		}/**/
		
		/**
		 * @overview: list of fields to be shown in editPage
		 * @returns: a numeric array with field codes
		 * @default: if commented out, all createPage fields will be used
		 */
/*		public function getEditFields(){
		}/**/
		
		/**
		 * @overview: get data for a commonList
		 * @returns: an array of rows, with each row being itself an array
		 * of 'field code => field value' pairs, i.e.:
		 * Array(
		 *		array('customer' => 'Nobody', 'address' => '53rd 1020'),
		 *		array('customer' => 'Some Guy', 'address' => 'homeless'),
		 * )
		 * @default: if commented out, commonList won't be available
		 */
		public function getCommonListData( $filters=array() ){
			$this->fixFilters(&$filters, array(
				'phone'			=> '`c`.`phone`',
				'address'		=> '`c`.`address`',
				'sellerName'	=> "CONCAT(`u`.`name`,' ',`u`.`lastName`)",
			));
			return "SELECT	`c`.*,
							CONCAT(`u`.`name`,' ',`u`.`lastName`) AS 'sellerName',
							`lc`.*
					FROM `customers` `c`
					LEFT JOIN `_users` `u` ON (`u`.`user` = `c`.`seller`)
					LEFT JOIN `_locations` `lc` USING (`id_location`)
					WHERE {$this->array2filter($filters)}
					AND {$this->getFilterFromModifier()}
					ORDER BY `c`.`customer`";
		}/**/
		
		/**
		 * @overview: get data for a simpleList
		 * @returns: an array of rows, with each row being itself an array
		 * of 'field code => field value' pairs, i.e.:
		 * Array(
		 *		array('customer' => 'Nobody', 'address' => '53rd 1020'),
		 *		array('customer' => 'Some Guy', 'address' => 'homeless'),
		 * )
		 * @default: if commented out, simpleList won't be available
		 */
/*		public function getSimpleListData( $filters=array() ){
		}/**/
		
		/**
		 * @overview: get data for a comboList
		 * @returns: a uni-dimensional associative array of pairs 'id => text',
		 * where id is the unique key for an item, and text is a descriptive
		 * name for the item (could be one field or a concatenation of fields),
		 * i.e.: Array(
		 *		18 => 'Nobody (address: 53rd 1020)',
		 *		26 => 'Some Guy (address: homeless)',
		 * )
		 * @default: if commented out, comboList data will be taken by calling
		 *           #getCommonListData() or #getSimpleListData() instead (first
		 *           of them that is not commented out). If one of those was
		 *           called before (and this one's commented out), it'll take
		 *           the data from cache.
		 */
		public function getComboListData( $filters=array() ){
			return "SELECT	`id_customer`,
							`customer`
					FROM `customers`
					WHERE {$this->getFilterFromModifier()}
					ORDER BY `customer`";
		}/**/
		
		
		
		/**
		 * @overview: a list of tools taken from available ones:
		 *            ~ create
		 *            ~ edit
		 *            ~ delete
		 *            ~ block
		 *            or define your own (see #additionalTools)
		 * @returns: a numeric array with tool codes, or (optionally) a
		 * string if only one tool is set.
		 * @default: if commented out, no tools will be present.
		 *           Notice that each tool not present means the action
		 *           is disabled too (edition, deletion, blocking).
		 *           Use code 'all' to include all available tools, or
		 *           'common' to add create, edit and delete.
		 * @notes: See ModulesBase@toolsBase for a list of built-in tools
		 */
		public function getTools(){
			return array('view', 'create', 'edit', 'delete');
		}/**/
		
		
		
		/**
		 * @overview: this function will receive the filters for lists, before
		 * calling #getCommonListData (or it's ancestors if it's missing), by reference.
		 * You can modify filters, or alter them to suit special situations.
		 * @returns: nothing
		 */
/*		public function checkFilter( &$filters ){
		}/**/
		
		/**
		 * @overview: this function will receive the data retrieved by #getCommonListData
		 * (or it's ancestors if it's missing), by reference. You can alter $data
		 * to suit special situations, but be sure that it remains a valid data
		 * source (see #getCommonListData notes for more info). If it becomes invalid, it
		 * will be silently discarded.
		 * @returns: nothing
		 */
/*		public function checkData( &$data ){
		}/**/
		
		
		
		/**
		 * @overview: if createPage or editPage are available, it is a good idea to
		 * set validation rules for each field. See Rules class for available rules,
		 * or create your own regular expressions to validate from (just add a slash
		 * before and after the expression).
		 * @returns: a ruleSet array.
		 *           A ruleSet is an array of 'field => rule' pairs, where field is
		 *           the field code and rule is either a preset rule or a regular
		 *           expression.
		 * @default: all fields that are not included (as keys) in the returned array
		 * are ignored for validation. Fields that are in the ruleSet but are not
		 * present in the received data are simply ignored, unless you uncomment
		 * #strictValidation and return true.
		 */
/*		public function validationRuleSet(){
		}/**/
		 
		/**
		 * @overview: Validates not only received fields but also that all fields
		 * defined in the ruleset are actually received.
		 * @returns: boolean true to set strict validation on
		 * @default: if commented out or returning anything but boolean true, strict
		 * validation is set to off (missing fields do not cause validation to fail).
		 */
/*		public function strictValidation(){
			return true;
		}/**/


/**
 * CUSTOM FUNCTIONS (write your own methods below when needed)
 */
		
		private function getFilterFromModifier(){
			switch( $this->modifier ){
				case 'customers': return 'NOT ISNULL(`since`)';
				case 'potential': return 'ISNULL(`since`)';
			}
			return '1';		# No filter for status (show all customers)
		}
		
	}

?>