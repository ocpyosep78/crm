<?php


	define('PATH_TO_LAYERS', dirname(__FILE__));
	define('LAYER_ERR_NOT_FOUND', 0);
	
	
	class Snippet_Layers{
	
		public function get( $layer ){
			
			return $this->exists($layer)
				? new $class			# Success
				: LAYER_ERR_NOT_FOUND;	# Failure
		
		}
		
		public function exists( $layer ){
		
			$path = PATH_TO_LAYERS."/{$layer}.layer.php";
			$class = "Layer_{$layer}";
			
			# Success
			if( is_file($path) ) require_once( $path );
			
			return class_exists($class);
			
		}
	
	}