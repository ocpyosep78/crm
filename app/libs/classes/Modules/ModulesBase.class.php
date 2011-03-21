<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	require_once( CONNECTION_PATH );
	
	
	/**
	 * A module represents a kind of object that's itself a list of objects. To avoid
	 * missunderstandings, here we'll refer to the objects represented by this class as
	 * modules, and items its individual objects. As an example, module Users represents
	 * all the users of the application, and each user is an item of that module.
	 * 
	 * Most modules share methods like 'list your items' or 'add an item', while their
	 * items often have methods 'create', 'view', 'edit', 'block', 'delete'. The goal of
	 * this class (and its children) is to grab what's common to all (or most) of the
	 * modules, ask external files about what's special or particular in a given Module,
	 * using a concise and compact protocol, and put it all together to answer for the
	 * abstract Module when the application requests something from it.
	 * 
	 * 
	 */


	abstract class ModulesBase extends Connection{
	
	
		protected $code;			/* unique identifier for this module */
		protected $modifier;		/* some modules might have different behaviours depending on a parameter */
		
		private $templateEngine;
		private $vars;
		
	
		public function __construct($code, $modifier=NULL){
		
			parent::__construct();
		
			$this->code = $code;
			$this->modifier = $modifier;
			
			$this->templateEngine = oSmarty();
			$this->vars = array();
		
		}
		
		/**
		 * This method is supposed to be overriden by this class' children
		 */
		public function getPage(){
		
			return '';
			
		}
		
		/**
		 * Returns an HTML string from a template, after assigning all vars
		 * passed as $data.
		 */
		protected function fetch($name, $data=array()){
		
			foreach( $data + $this->vars as $k => $v ) $this->templateEngine->assign($k, $v);
			
			$dir = dirname(__FILE__).'/templates';
			if( !is_file("{$dir}/{$name}.tpl") ) $name = '404';
		
			return $this->templateEngine->fetch( "{$dir}/{$name}.tpl" );
		
		}
		
		protected function assign($var, $val=NULL){
			
			$this->vars[$var] = $val;
			
		}
		
		protected function clearVars($var, $val=NULL){
		
			$this->vars = array();
			
		}
	
	
	}

?>