<?php


	class Navigation_Layer_Client{
	
		public function display($msg, $type='error'){
		
			showStatus($msg, $type);
			
			return $this;
		
		}
	
		public function write($HTML, $where=NULL){
		
			is_null( $where )
				|| oXajaxResp()->addAssign($where, 'innerHTML', $HTML);
			
			return $this;
		
		}
		
		public function __toString(){
		
			return oXajaxResp();
		
		}
	
	}