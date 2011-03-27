<?php

	class Modules_ajaxEngine{
	
		public $AjaxResponse;
		
		public function __construct(){
		
			$this->AjaxResponse = oXajax();
		
		}
		
		public function write($element, $text){
		
			return addAssign($element, 'innerHTML', $text);
		
		}
		
		public function addScript( $script ){
		
			return addScript( $script );
		
		}
		
		public function getResponse(){
		
			return oXajaxResp();
		
		}
		
		public function displayError( $msg ){
			
			return showStatus( $msg );
			
		}
		
	}

?>