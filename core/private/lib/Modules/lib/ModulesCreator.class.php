<?php

	class ModulesCreator{
	
		private $type;
		private $code;
		private $modifier;
		private $params;
	
		private $Handlers=array();	/* Handlers involved in fetching this page */
		
		
		public function __construct($type, $code, $modifier=NULL, $params=NULL){
			
			$this->type = $type;
			$this->code = $code;
			$this->modifier = $modifier;
			$this->params = $params;
			
		}
		
		/**
		 * Returns a formatted error (HTML string)
		 */
		private function Error( $msg ){
			
			$Error = new ModulesError;
			
			return $Error->fetch( $msg );
			
		}
		
		/**
		 * @overview: Validates requested page type, makes sure current code
		 *            is valid, creates the right object to handle that page
		 *            creation and calls its getPage() method to get the page's
		 *            HTML.
		 * @returns: an HTML string
		 */
		public function getPage(){
			
			$HTML = '';
			
			switch($this->type){
				case 'commonList':
					$HTML .= $this->getElement('bigTools');
				case 'info':
				case 'create':
				case 'edit':
					$HTML .= $this->getElement('comboList');
				break;
			}
		
			$HTML .= $this->getElement();
			$this->params['page_uID'] = microtime();
			
			return $HTML;
			
		}
		
		/**
		 * @overview: given a $type, requests a handler for it, and calls its
		 *            getElement method.
		 * @returns: an HTML string
		 */
		public function getElement( $type=NULL ){
		
			$oldType = $this->type;
		
			if( count(func_get_args()) ) $this->type = $type;
			
			if(!$this->code || !$this->type){
				$this->type = $oldType;
				return $this->Error('ModulesCreator error: missing params');
			}
		
			# See if we have a handler (and a code given, as it's required)
			$hdl = $this->Handlers[] = $this->getHandler();
			
			# Restore type
			$this->type = $oldType;
			
			if( !$hdl ){
				return $this->Error('ModulesCreator error: requested element is not defined');
			}
			
			# Initialize handler
			$hdl->initialize()->setCommonProperties()->feedTemplate();
			
			return $this->getCurrentHandler()->getElement();
		
		}
		
		/**
		 * @overview: Most pages will need to get a javascript run after they're
		 *            done modifying their HTML. This and other actions should be
		 *            included in a method (of the handler) called doTasks.
		 * @returns: NULL
		 */
		public function doTasks(){
		
			foreach( $this->Handlers as $Handler ){
				if( $Handler ) $Handler->doTasks();
			}
			
		}
		
		/**
		 * @overview: Different classes handle different possible pages Modules
		 *            can build, so we forward the request to the right handler.
		 *            This method finds which one it is, depending on the type
		 *            of page we're asking for.
		 * @returns: a string with the name of the appropriate handler class
		 */
		private function getHandler( $type=NULL ){
		
			if( is_null($type) ) $type = $this->type;
			
			switch( $type ){
				case 'commonList':
				case 'innerCommonList':
				case 'simpleList':
					$handler = 'Lists';
					break;
				case 'create':
				case 'edit':
					$handler = 'Edit';
					break;
				case 'info':
					$handler = 'Info';
					break;
				case 'comboList':
				case 'bigTools':
					$handler = 'Widgets';
					break;
				default:
					$handler = NULL;
			}
			
			# Return new object on success
			if( $handler && is_file($path=dirname(__FILE__)."/../handlers/Mod_h_{$handler}.class.php") ){
				require_once( $path );
				if( class_exists($class="Mod_h_{$handler}") ){
					return new $class($type, $this->code, $this->modifier, $this->params);
				}
			}
			
			# Failure
			return NULL;
			
		}
		
		private function getCurrentHandler(){
		
			$cnt = count($this->Handlers);
			
			return $cnt ? $this->Handlers[$cnt-1] : NULL;
			
		}
	
	}

?>