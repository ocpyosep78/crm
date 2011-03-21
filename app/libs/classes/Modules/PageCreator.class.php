<?php

	class PageCreator{
		
		/**
		 * Validates requested page type, makes sure current code is valid
		 * creates the right object to handle that page creation and calls
		 * its getPage method to get the page's HTML, which it returns.
		 */
		public function getPage($code, $type, $modifier=NULL){
		
			if( !$code ) return '';
			
			switch( $type ){
				case 'commonList':
				case 'simpleList':
				case 'comboList':
					require_once( dirname(__FILE__).'/pages/Module_Lists.class.php' );
					$handler = 'Module_Lists';
					break;
				case 'create':
				case 'edit':
					require_once( dirname(__FILE__).'/pages/Module_Edit.class.php' );
					$handler = 'Module_Edit';
					break;
				case 'info':
					require_once( dirname(__FILE__).'/pages/Module_Info.class.php' );
					$handler = 'Module_Info';
					break;
				default: return '';
			}
			
			$object = new $handler($code, $modifier);
			
			return $object->getPage( $type );
		
		}
	
	}

?>