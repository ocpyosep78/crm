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

	
	class Mod_sales extends ModulesDefaults{
		
		/**
		 * @overview: The name that will be shown to users for this object
		 * @returns: string
		 * @default: defaults to '' if commented out
		 */
		public function getName(){
		
			return gettext('Venta');
			
		}/**/
		
		/**
		 * @overview: The name that will be shown to users for this object,
		 * when plural
		 * @returns: string
		 * @default: defaults to '' if commented out
		 */
		public function getPlural(){
		
			return gettext('Ventas');
			
		}/**/
		
		
		
		/**
		 * @overview: The list of attributes for each field that could be used by Modules.
		 * @returns: Array of code => attributes arrays. Attributes might have the following fields:
				name		Screen name for this field
				class		a class to add to this field, intended for CSS purposes
				type		From: text, image, combo (defaults to text)
							image will produce an image in infoPage, a file input in createPage and both in editPage
							for lists, images will be shown miniaturized in lists, and link to the image in _blank page
							combo will produce text in infoPage, a combo in both createPage and editPage
							for lists, combo will add a special search showing only possible values to choose from
				hide		whether to hide this field in infoPage
		 * all attributes are optional, but field will be ignored in lists if name is empty
		 * @default: if empty, keys will be taken as fields, with default attributes (except hidden)
		 *           if keys are not defined either, then an error page will be shown instead
		 */
		public function getFields(){
		
			return array(
				'type'			=> 'Tipo',
				'date'			=> 'Fecha',
				'invoice'		=> 'Nº Factura',
				'customer'		=> 'Cliente',
				'notes'			=> 'Descripción / Notas',
				'id_sale'		=> 'ID',
				'id_customer'	=> 'ID de Cliente',
				'technician'	=> 'Técnico',
			);
			
		}/**/
		
		/**
		 * @overview: Returns the key(s) that identify each item, named by
		 * field code
		 * @returns: string or array of strings
		 * @default: if commented out or empty, the following pages will be
		 *           unavailable: infoPage, editPage
		 * Notice that keys will be hidden by default, unless field has hidden
		 * key explicitly set to false.
		 */
		public function getKeys(){
		
			return 'id_sale';
			
		}/**/
		
		/**
		 * @overview: The code of the field that will be shown as a tooltip
		 * for the row, in lists.
		 * @returns: string
		 * @default: defaults to '' if commented out (no tooltip shown)
		 */
		public function getTipField(){
		
			return 'description';
			
		}/**/
		
		
		
		/**
		 * @overview: list of fields to be shown in commonList
		 * @returns: a numeric array with field codes
		 * @default: if empty, commonList won't be available, unless
		 *           #getSimpleListFields is also empty, in which case
		 *           commonList will be available and it will have all
		 *           defined fields, except keys
		 */
		public function getCommonListFields(){
		
			return array('date', 'invoice', 'customer', 'notes');
			
		}/**/
		
		/**
		 * @overview: list of fields to be shown in simpleList
		 * @returns: a numeric array with field codes
		 * @default: if commented out, simpleListPage won't be available
		 */
/*		public function getSimpleListFields(){
		}/**/
		
		/**
		 * @overview: field to be shown in comboList as text
		 * @returns: string
		 * @default: if commented out, it defaults to 'comboField'
		 *           Returning any falsy value or any field that's not
		 *           present in data returned by #getComboListData (that
		 *           includes 'comboField') will make comboList useless
		 *           and therefore it won't be shown.
		 */
/*		public function getComboListFields(){
		}/**/
		
		/**
		 * @overview: list of fields to be shown in infoPage
		 * @returns: a numeric array with field codes
		 * @default: if commented out, all defined fields will be used,
		 * except keys and hidden fields.
		 */
		public function getInfoFields(){
		
			return array('date', 'invoice', 'customer', 'notes');
			
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
		 * Array( array('customer' => 'Nobody', 'address' => '53rd 1020'),
		 *        array('customer' => 'Some Guy', 'address' => 'homeless'))
		 * @default: if commented out, commonList will be empty
		 */
		public function getCommonListData( $filters=array() ){
			# Handle possible name conflicts and composed fields
			$this->fixFilters(&$filters, array(
				'date'		=> "DATE_FORMAT(`l`.`date`, '%d/%m/%Y')",
			));
			return "SELECT	`l`.*,
							DATE_FORMAT(`l`.`date`, '%d/%m/%Y') AS 'date',
							`c`.`customer`,
							CONCAT(
								'(', DATE_FORMAT(`l`.`date`, '%d/%m/%Y'), ') ',
								'Fact. ', `l`.`invoice`, ' - ',
								`c`.`customer`
							) AS 'comboField'
					FROM `sales` `l`
					LEFT JOIN `customers` `c` USING (`id_customer`)
					WHERE {$this->array2filter($filters)}
					AND `type` = '{$this->modifier}'
					ORDER BY `l`.`date`, CONVERT(`l`.`invoice`, UNSIGNED INTEGER)";
		}/**/
		
		/**
		 * @overview: get data for a simpleList
		 * @returns: an array of rows, with each row being itself an array
		 * of 'field code => field value' pairs, i.e.:
		 * Array( array('customer' => 'Nobody', 'address' => '53rd 1020'),
		 *        array('customer' => 'Some Guy', 'address' => 'homeless'))
		 * @default: if commented out, simpleList page will take data
		 *           from #getCommonListData instead
		 */
/*		public function getSimpleListData( $filters=array() ){
		}/**/
		
		/**
		 * @overview: get data for a comboList
		 * @returns: a uni-dimensional associative array of pairs 'id => text',
		 * where id is the unique key for an item, and text is a descriptive
		 * name for the item (could be one field or a concatenation of fields),
		 * i.e.: Array( 18 => 'Nobody (address: 53rd 1020)',
		 *              26 => 'Some Guy (address: homeless)')
		 * @default: if commented out, comboList data will be taken by calling
		 *           #getCommonListData() or #getSimpleListData() instead (first
		 *           of them that is not commented out). If one of those was
		 *           called before (and this one's commented out), it'll take
		 *           the data from cache.
		 */
/*		public function getComboListData( $filters=array() ){
		}/**/
		
		/**
		 * @overview: get data for infoPage
		 * @returns: a uni-dimensional associative array of pairs 'field => value'
		 * i.e.: Array( 'name' => 'None',
		                'address' => '53rd 1020' )
		 * @default: if commented out, infoPage data will be taken by calling
		 *           #getCommonListData() or #getSimpleListData() instead (first
		 *           of them that is not commented out) with the id as filter.
		 */
/*		public function getInfoPageData( $filters=array() ){
		}/**/
		
		
		
		/**
		 * @overview: a list of tools taken from available ones:
		 *  ~ create
		 *	~ edit
		 *	~ delete
		 *	~ block
		 * See ModulesBase#tools for an updated list of available tools
		 * @returns: a numeric array with tool codes, or (optionally) a
		 * string if only one tool is set.
		 * @default: if commented out, no tools will be present.
		 *           Notice that each tool not present means the action
		 *           is disabled too (edition, deletion, blocking).
		 *           Use code 'all' to include all available tools, or
		 *           'common' to add create, edit and delete.
		 */
		public function getListTools(){
		
			return 'common';
			
		}/**/
		
		
		
		/**
		 * @overview: this function will receive the filters for lists, before
		 * calling #getCommonListData (or it's ancestors if it's missing), by reference.
		 * You can modify filters, or alter them to suit special situations.
		 * @returns: nothing
		 */
		public function checkFilter( &$filters ){
	
			if( empty($filters['type']) ) return;
		
			/* Get possible 'translated' types and build RegExp with the right wildcards  */
			$types = oLists()->salesTypes();
			$typeRE = '/'.str_replace(array('\*', '%'), '.*', preg_quote($filters['type'])).'/i';
			
			# See if search term matches any of the screen names for types
			foreach( $types as $k => &$v ) if( preg_match($typeRE, $v) ) return $filters['type'] = $k;
			
			return $filters['type'] = '.';	/* Make sure no accidental match happens */
			
		}/**/
		
		/**
		 * @overview: this function will receive the data retrieved by #getCommonListData
		 * (or it's ancestors if it's missing), by reference. You can alter $data
		 * to suit special situations, but be sure that it remains a valid data
		 * source (see #getCommonListData notes for more info). If it becomes invalid, it
		 * will be silently discarded.
		 * @returns: nothing
		 */
		public function checkData( &$data ){
		
			/* Get all 'translated' values for type */
			$types = oLists()->salesTypes();
		
			foreach( $data as &$row ){
				if( empty($row['invoice']) ) $row['invoice'] = '(sin especificar)';
				if( isset($types[$row['type']]) ) $row['type'] = $types[$row['type']];
			}
			
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
		public function validationRuleSet(){
			return array(
				'id_customer'	=> array('selection'),
				'invoice'		=> array('num', 4, 5),
				'date'			=> array('date', 1, NULL),
				'warranty'		=> array('selection'),
				'id_system'		=> array('selection'),
				'id_installer'	=> array('selection'),
				'technician'	=> array('selection'),
				'description'	=> array('text', NULL, 120),
			);
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
		
	}

?>