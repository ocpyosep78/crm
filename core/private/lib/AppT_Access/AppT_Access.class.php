<?php

	class AppT_access{
	
		/* TEMP while Permits class is merged into Access library */
		public function can( $what ){
		
			return oPermits()->can( $what );
		
		}
	
	}

?>