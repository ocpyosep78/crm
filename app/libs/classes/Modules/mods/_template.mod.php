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

	
	class Mod_{code here} extends ModulesDefaults{
		
		/**
		 * @overview: The name that will be shown to users for this object
		 * @returns: string
		 * @default: defaults to '' if commented out
		 */
/*		public function getName(){
		}/**/
		
		/**
		 * @overview: The name that will be shown to users for this object,
		 * when plural
		 * @returns: string
		 * @default: defaults to '' if commented out
		 */
/*		public function getPlural(){
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
				isKey		whether this field is a key
				hide		whether to hide this field in infoPage
		 * all attributes are optional, but field will be ignored in lists if name is empty
		 * fields flagged as keys (isKey === true) will be hidden by default. Ser hide === false explicitly to override
		 * by default, infoPage, createPage and editPage will use all fields that are not hidden
		 * to override this behaviour, use getCreateFields or getEditFields (infoPage cannot override it)
		 * if one of these is created and the other one isn't, both will point to the one that is defined
		 */
/*		public function getFields(){
		}/**/
		
		/**
		 * @overview: Returns the key(s) that identify each item, named by
		 * field code
		 * @returns: string or array of strings
		 * @default: if commented out or empty, the following pages will be
		 *           unavailable: infoPage, editPage
		 */
/*		public function getKeys(){
		}/**/
		
		/**
		 * @overview: The code of the field that will be shown as a tooltip
		 * for the row, in lists.
		 * @returns: string
		 * @default: defaults to '' if commented out (no tooltip shown)
		 */
/*		public function getTipField(){
		}/**/
		
		
		
		/**
		 * @overview: list of fields to be shown in commonList
		 * @returns: a numeric array with field codes
		 * @default: if commented out, no page will be available
		 */
/*		public function getCommonListFields(){
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
		 * @default: if commented out, comboList won't be available
		 */
/*		public function getComboListFields(){
		}/**/
		
		/**
		 * @overview: list of fields to be shown in infoPage
		 * @returns: a numeric array with field codes
		 * @default: if commented out, all defined (and not hidden) fields
		 * will be used
		 */
/*		public function getInfoFields(){
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
/*		public function getCommonListData( $filters=array() ){
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
		 * #getCommonListData() or #getSimpleListData() instead (first of them that
		 * is not commented out), if comboList
		 */
/*		public function getComboListData( $filters=array() ){
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
/*		public function getListTools(){
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
		
	}

?>