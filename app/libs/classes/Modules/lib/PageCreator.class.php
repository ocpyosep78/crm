<?php

	class PageCreator{
	
		private $Handler=NULL;		/* Class that will handle page creation */
		
		/**
		 * Validates requested page type, makes sure current code is valid
		 * creates the right object to handle that page creation and calls
		 * its getPage method to get the page's HTML, which it returns.
		 */
		public function getPage($type, $code, $modifier=NULL){
		
			if( !$code ) return '';
			
			switch( $type ){
				case 'commonList':
				case 'simpleList':
					require_once( dirname(__FILE__).'/../pages/Module_Lists.class.php' );
					$handler = 'Module_Lists';
					break;
				case 'create':
				case 'edit':
					require_once( dirname(__FILE__).'/../pages/Module_Edit.class.php' );
					$handler = 'Module_Edit';
					break;
				case 'info':
					require_once( dirname(__FILE__).'/../pages/Module_Info.class.php' );
					$handler = 'Module_Info';
					break;
				default: return '';
			}
			
			$this->Handler = new $handler($type, $code, $modifier);
			
			return $this->Handler->getPage();
		
		}
		
		public function doTasks(){
		
			if( $this->Handler ) return $this->Handler->doTasks();
			
		}
	
	}

?>