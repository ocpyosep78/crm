<?php

	class SnippetLayer_modules{
	
		public function getModule($code, $params=array()){
		
			$Mod = $this->createModule( $code );
			
			# Initiate required properties of the object
			# Have it aquire data and fill gaps in the definition file
			return $Mod->inject($code, $params)->aquire();
		
		}
		
		public function createModule( $code ){
			
			$file = SNIPPET_DEFINITION_PATH."/{$code}.def.php";
			$class = "Snippet_def_{$code}";
			
			# Attempt to load the definition file
			if( is_file($file) ) require_once( $file );
			if( class_exists($class) ) return new $class;
			# If not found, use the naked Source as $Source
			else return new Snippets_Handler_Source;
			
		}
	
	}

?>