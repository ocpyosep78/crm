<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	define('MODULES_PATH', dirname(__FILE__).'/mods/');
	define('MODULES_TEMPLATES_PATH', dirname(__FILE__).'/static/templates/');
	

	require_once( CONNECTION_PATH );
	
	require_once( dirname(__FILE__).'/engines/ajax.engine.php' );
	require_once( dirname(__FILE__).'/engines/template.engine.php' );
	
	require_once( dirname(__FILE__).'/lib/ModulesBase.class.php' );
	require_once( dirname(__FILE__).'/lib/ModulesDefaults.class.php' );
	
	require_once( dirname(__FILE__).'/lib/PageChecker.class.php' );
	require_once( dirname(__FILE__).'/lib/PageCreator.class.php' );
	

	class Modules{
	
		private $PageChecker;
		private $PageCreator;
		private $AjaxEngine;
		
		public function __construct( $code=NULL ){
		
			$this->PageChecker = new PageChecker;
			$this->PageCreator = new PageCreator;
			$this->AjaxEngine = new Modules_ajaxEngine;
		
		}
	
		/**
		 * Whether a page can be built. Takes a single argument that's assumed
		 * to be a page name (i.e. usersInfo, customersEdit, etc.).
		 */
		public function canBuildPage( $page ){
			
			return $this->PageChecker->canBuildPage( $page );
			
		}
		
		public function getPage($name, $modifier=NULL, $filters=array()){
			
			# Not checking $page is on purpose, so it raises a warning in developer mode
			# if that page cannot be created by PageCreator. We assume that calling
			# #getPage is done AFTER checking: either input is valid or something's wrong.
			$page = $this->PageChecker->parsePageName( $name );
			
			return $this->PageCreator->getPage($page['type'], $page['code'], $modifier, $filters);
			
		}
		
		public function doTasks( $filters=array() ){
			
			$this->PageCreator->doTasks( $filters );
			
			return $this->AjaxEngine->AjaxResponse;
			
		}
		
		public function printPage($name, $modifier=NULL, $filters=array()){
		
			$HTML = $this->getPage($name, $modifier, $filters);
			if( !$HTML ) return NULL;
			
			$this->AjaxEngine->write(PAGE_CONTENT_BOX, $HTML);
			
			return $this->doTasks( $filters );
		
		}
	
	}

?>