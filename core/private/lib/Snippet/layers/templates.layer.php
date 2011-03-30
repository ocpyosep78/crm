<?php

	class SnippetLayer_templates{
		
		private $engine;
	
		public function __construct(){
			
			$this->engine = oSmarty();
		
		}
		
		public function assign($var, $val){
		
			return $this->engine->assign($var, $val);
		
		}
		
		public function fetch( $template ){
		
			$template = realpath( SNIPPET_TEMPLATES.DIRECTORY_SEPARATOR.$template );
		
			return $this->engine->fetch( $template );
		
		}
	
	}

?>