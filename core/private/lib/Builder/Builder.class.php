<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	
	return ($Builder = new Builder());
	
	

	/* Shortcuts to global objects */
	function getBuilderObject($obj, $code=NULL){
	
		return $GLOBALS['Builder']->get($obj, $code);
		
	}
	
	function oModules( $code=NULL )		{	return getBuilderObject('Modules', $code);		}
	function oLists( $code=NULL )		{	return getBuilderObject('Lists', $code);		}		/* TEMP untill Modules is ready */
	function oStats( $code=NULL )		{	return getBuilderObject('Stats', $code);		}		/* TEMP untill Modules is ready */
	function oFormTable( $code=NULL )	{	return getBuilderObject('FormTable', $code);	}		/* TEMP untill Modules is ready */
	
	function oXajax()					{	return getBuilderObject( 'Xajax' );				}
	function oXajaxResp()				{	return getBuilderObject( 'XajaxResp' );			}
	function oSmarty()					{	return getBuilderObject( 'Smarty' );			}
	
	function oSQL()						{	return getBuilderObject( 'SQL' );				}
	function oNav()						{	return getBuilderObject( 'Nav' );				}
	function oPageCfg()					{	return getBuilderObject( 'PageCfg' );			}
	function oPermits()					{	return getBuilderObject( 'Permits' );			}
	
	function oTabs()					{	return getBuilderObject( 'Tabs' );				}
	
	function oAlerts()					{	return getBuilderObject( 'Alerts' );			}
	function oValidate()				{	return getBuilderObject( 'Validate' );			}
	function oPajax()					{	return getBuilderObject( 'Pajax' );				}
	
	
	
	class Builder{
	
		private $objects;
		
		/**
		 * Constructor: initialize private member objects
		 */
		public function __construct(){
		
			$this->objects = array();
			
		}
		
		/**
		 * Get is the only public method of this class. It returns the stored object if found
		 * or creates one, stores it for later calls, and returns it.
		 */
		public function get($obj, $code=NULL, $forceNew=false){
		
			return !$forceNew && isset($this->objects[$obj])
			  && (is_null($code) || $this->objects[$obj]['code'] === $code)
				? $this->objects[$obj]['object']
				: $this->build($obj, $code);
			
		}
		
		
		/**
		 * Builds an object given its name, if it's predefined in Builder class. Path to the class
		 * definition is expected to be well-formatted, {$name}/{$name}.class.php, in CLASSES_PATH.
		 * If the object class is not defined in Builder, or for some reason the class definition
		 * cannot be loaded, an error will be triggered and NULL returned.
		 */
		private function build($obj, $code){
			
			# Load all classes this class depends on (if any)
			$this->loadDependencies( $obj );
		
			$classPath = $this->classPath( $obj );
			
			# Make sure the file with the class definition is reachable or abort
			if( !is_file($classPath) ){
				trigger_error("BUILDER ERROR: Object {$obj} is not registered or its path is incorrect ({$classPath})");
				return NULL;
			}
			
			# Include class definition file
			require_once( $classPath );
			
			# Initiate the object of class $obj
			switch( $obj ){
				case 'Alerts':
					$Alerts = new Alerts( getSes('user') );
				break;
				case 'FormTable':
					$FormTable = new FormTable;
				break;
				case 'Lists':
					$Lists = new Lists;	/* TEMP */
				break;
				case 'Modules':
					$Modules = new Modules( $code );
				break;
				case 'Nav':
					$Nav = new Nav;
				break;
				case 'PageCfg':
					$PageCfg = new PageCfg;
				break;
				case 'Pajax':
					$Pajax = new Pajax;
				break;
				case 'Permits':
					$Permits = new Permits();
					$Permits->setUser( getSes('user') );
					$Permits->setProfile( getSes('id_profile') );
					$Permits->setTimeOut( PERMITS_CACHE_TIMEOUT );		/* Keep cached lists up to 30 minutes */
					$Permits->setSuperProfile( 1 );						/* Master (profile 1) has access to everything (devel) */
				break;
				case 'Smarty':
					$Smarty = new Smarty;
					$Smarty->setTemplateDir(TEMPLATES_PATH);
					$Smarty->setCompileDir('temp');
					$Smarty->setCacheDir(SMARTY_DIR.'cache');
					$Smarty->setConfigDir(SMARTY_DIR.'configs');
				break;
				case 'SQL':
					$SQL = new SQL();
					$SQL->setTimeZone(TIME_ZONE);
				break;
				case 'Stats':
					$Stats = new Stats();
					$Stats->setTimeZone(TIME_ZONE);
				break;
				case 'Tabs':
					$Tabs = new Tabs;
				break;
				case 'Xajax':
					$Xajax = new xajax("", "xajax_", 'ISO-8859-1');
					$Xajax->outputEntitiesOn();
					$Xajax->decodeUTF8InputOn();
				case 'XajaxResp':
					$XajaxResp = new xajaxResponse;
				break;
				case 'Validate':
					$Validate = new Validate;
				break;
				default:
					${$obj} = NULL;			/* Class is not expected by Builder */
					trigger_error("BUILDER ERROR: Object {$obj} is not registered in this class");
				break;
				
			}
			
			# Store newly created object in local list of objects for later retrieval
			$this->objects[$obj] = array('object' => ${$obj}, 'code' => $code);
			
			return $this->objects[$obj]['object'];
			
		}
		
		/**
		 * Some classes might make it hard to keep the right path structure, specially
		 * if it's a secondary class of a big library (like XajaxResp from Xajax lib)
		 * Just add those exceptions to the list, in the switch, returning the right
		 * path as a string (from base directory, above app/).
		 */
		private function classPath( $obj ){
			
			switch( $obj ){
				case 'fPDF':
					return THIRD_PARTY_PATH.'fPDF/ExtendedFPDF.class.php';
				break;
				case 'FormTable':
				case 'Lists':
				case 'PageCfg':
				case 'Pajax':
				case 'SQL':
				case 'Validate':
					return CLASSES_PATH."{$obj}/{$obj}.class.php";
				case 'Smarty':
				case 'Xajax':
					return CORE_THIRD_PARTY."{$obj}/{$obj}.class.php";
				case 'XajaxResp':
					return CORE_THIRD_PARTY.'Xajax/xajaxResponse.inc.php';
				break;
				default:
					return CORE_LIB."{$obj}/{$obj}.class.php";
				break;
			}
			
		}
		
		/**
		 * If a class requires another class to be instantiated (or at least loaded)
		 * add an entry to the switch under that class, and assign dependencies to
		 * $list var (always an array, even if it's a single element array).
		 */
		private function loadDependencies( $obj ){
			
			switch( $obj ){
				case 'XajaxResp': $list = array('Xajax');
			}
			
			if( isset($list) && is_array($list) ){
				foreach( $list as $item ) $this->get($item);
			}
			
		}
		
	}
	
?>