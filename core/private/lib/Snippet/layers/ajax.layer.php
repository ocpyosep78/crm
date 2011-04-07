<?php

	class SnippetLayer_ajax{
		
		public function write($element, $text){
			
			return addAssign($element, 'innerHTML', $text);
		
		}
		
		public function addScript( $script ){
		
			return addScript( $script );
		
		}
		
		public function display($msg, $type='error'){
		
			return showStatus($msg, $type);
			
		}
		
		public function addReload($msg='', $type=0){
		
			return oNav()->reloadPage($msg, $type);
			
		}
		
		public function getResponse(){
		
			return oXajaxResp();
		
		}
	
	}

?>