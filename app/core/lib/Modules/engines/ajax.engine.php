<?php

	class Modules_ajaxEngine{
	
		public $AjaxResponse;
		
		public function __construct(){
			
			$this->AjaxResponse = oXajaxResp();
			
		}
		
		public function write($element, $text){
		
			return addAssign($element, 'innerHTML', $text);
		
		}
		
	}

?>