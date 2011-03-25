<?php

	class Modules_templateEngine{
		
		private $engine;
	
		public function __construct(){
			
			$this->engine = oSmarty();
		
		}
		
		public function assign($var, $val){
		
			return $this->engine->assign($var, $val);
		
		}
		
		public function fetch( $template ){
		
			$template = MODULES_TEMPLATES.$template;
		
			return $this->engine->fetch( $template );
		
		}
		
	}

?>