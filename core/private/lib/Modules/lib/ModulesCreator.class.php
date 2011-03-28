<?php

	class ModulesCreator{
	
		private $type;
		private $code;
		private $modifier;
		private $params;
	
		private $Handlers=array();	/* Handlers involved in fetching this page */
		
		
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
		public function getPage($type, $code, $modifier=NULL, $params=NULL){
		
			$HTML = $this->getElement($type, $code, $modifier, $params);
			
			# Add comboList to 'commonList', 'info', 'create', 'edit' pages
			if( in_array($type, array('commonList', 'info', 'create', 'edit')) ){
				$comboHTML = $this->getElement('comboList', $code, $modifier, $params);
				$HTML = $comboHTML.$HTML;
			}
			
			return $HTML;
			
		}
		
		/**
		 * @overview: given a $type, requests a handler for it, and calls its
		 *            getElement method.
		 * @returns: an HTML string
		 */
		public function getElement($type, $code, $modifier=NULL, $params=NULL){
			
			# Store main parameters in case we need them later
			$this->storeProperties($type, $code, $modifier, $params);
		
			# See if we have a handler (and a code given, as it's required)
			$this->Handlers[] = $this->getHandler();
			if(!$code || !$this->getCurrentHandler()){
				return $this->Error('ModulesCreator error: requested element is not defined');
			}
			
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
				case 'comboList':
					require_once( dirname(__FILE__).'/../handlers/Mod_h_Lists.class.php' );
					$handler = 'Mod_h_Lists';
					break;
				case 'create':
				case 'edit':
					require_once( dirname(__FILE__).'/../handlers/Mod_h_Edit.class.php' );
					$handler = 'Mod_h_Edit';
					break;
				case 'info':
					require_once( dirname(__FILE__).'/../handlers/Mod_h_Info.class.php' );
					$handler = 'Mod_h_Info';
					break;
				default:
					$handler = NULL;
			}
			
			return is_null($handler)
				? NULL
				: new $handler($type, $this->code, $this->modifier, $this->params);
			
		}
		
		private function getCurrentHandler(){
		
			$cnt = count($this->Handlers);
			
			return $cnt ? $this->Handlers[$cnt-1] : NULL;
			
		}
		
		/**
		 * @overview: Store main parameters for future reference
		 * @returns: NULL
		 */
		private function storeProperties($type, $code, $modifier, $params){
			
			$this->type = $type;
			$this->code = $code;
			$this->modifier = $modifier;
			$this->params = $params;
			
		}
	
	}

?>