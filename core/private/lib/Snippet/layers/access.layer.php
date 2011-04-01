<?php

	class SnippetLayer_access{
	
		public function can( $what ){
		
			return oPermits()->can( $what );
		
		}
	
		public function cant( $what ){
		
			return !$this->can( $what );
		
		}
		
	}

?>