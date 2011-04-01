<?php


	class Navigation_Layer_User{
	
		public function can( $what ){
		
			return oPermits()->can( $what );
		
		}
	
		public function cant( $what ){
		
			return !$this->can( $what );
		
		}
	
	}


?>