<?php

	class Modules_templateEngine{
		
		public function assign($var, $val){
		
			return oSmarty()->assign($var, $val);
		
		}
		
		public function fetch( $template ){
		
			return oSmarty()->fetch( $template );
		
		}
		
	}

?>