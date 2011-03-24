<?php

	class PageCreator{
	
		private $type;
		private $code;
		private $modifier;
	
		private $Handlers=array();	/* Handlers involved in fetching this page */
		
		/**
		 * @overview: Validates requested page type, makes sure current code
		 *            is valid, creates the right object to handle that page
		 *            creation and calls its getPage() method to get the page's
		 *            HTML.
		 * @returns: an HTML string
		 */
		public function getPage($type, $code, $modifier=NULL, $params=NULL){
		
			$args = func_get_args();
			$HTML = $this->getElement($type, $code, $modifier, $params);
			
			# Add comboList to 'commonList', 'info', 'create', 'edit' pages
			if( in_array($type, array('commonList', 'info', 'create', 'edit')) ){
				$comboHTML = $this->getElement('comboList', $code, $modifier, $params);
				$HTML = $comboHTML.$HTML;
			}
			
			return $HTML;
			
		}
		
		public function getElement($type, $code, $modifier=NULL, $params=NULL){
			
			# Store main parameters in case we need them later
			$this->storeMainParams($type, $code, $modifier);
		
			# See if we have a handler (and a code given, as it's required)
			$this->Handlers[] = $this->getHandler();
			if(!$code || !$this->getCurrentHandler()) return NULL;
			
			# Get the actual HTML
			return $this->getCurrentHandler()->getElement( $params );
		
		}
		
		/**
		 * @overview: Ajax calls might be a lot less standard, so here we let
		 *            the code in the right handler class handle it entirely.
		 *            We simply pass all received parameters and forget about
		 *            it. 
		 * @returns: As in #getPage, we call and return Handler#doTasks
		 */
		public function runAjaxCall($type, $code, $modifier=NULL, $params=NULL){
		
			# Store main parameters in case we need them later
			$this->storeMainParams($type, $code, $modifier);
		
			# See if we have a handler (and a code given, as it's required)
			$this->Handlers[] = $this->getHandler();
			
			# Pass params to the handler, and it's not our problem anymore
			if( $code && $this->getCurrentHandler() ){
				$this->getCurrentHandler()->runAjaxCall( $params );
			}
		
		}
		
		/**
		 * @overview: Most pages will need to get a javascript run after they're
		 *            done modifying their HTML. This and other actions should be
		 *            included in a method (of the handler) called doTasks. It'll
		 *            be sent the $params var (which is a wildcard param).
		 * @returns: NULL
		 */
		public function doTasks( $params=NULL ){
		
			foreach( $this->Handlers as $Handler ) $Handler->doTasks( $params );
			
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
				case 'updateCommonList':
				case 'simpleList':
				case 'comboList':
					require_once( dirname(__FILE__).'/../pages/Module_Lists.class.php' );
					$handler = 'Module_Handler_Lists';
					break;
				case 'create':
				case 'edit':
					require_once( dirname(__FILE__).'/../pages/Module_Edit.class.php' );
					$handler = 'Module_Handler_Edit';
					break;
				case 'info':
					require_once( dirname(__FILE__).'/../pages/Module_Info.class.php' );
					$handler = 'Module_Handler_Info';
					break;
				default:
					$handler = NULL;
			}
			
			return is_null($handler)
				? NULL
				: new $handler($type, $this->code, $this->modifier, $this);
			
		}
		
		private function getCurrentHandler(){
		
			$cnt = count($this->Handlers);
			
			return $cnt ? $this->Handlers[$cnt-1] : NULL;
			
		}
		
		/**
		 * @overview: Store main parameters for future reference
		 * @returns: NULL
		 */
		private function storeMainParams($type, $code, $modifier){
			
			$this->type = $type;
			$this->code = $code;
			$this->modifier = $modifier;
			
		}
	
	}

?>