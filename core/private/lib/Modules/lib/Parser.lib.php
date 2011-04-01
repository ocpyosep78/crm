<?php

	require_once(dirname(__FILE__).'/Definitions.lib.php');

	class Modules_Parser{
		
		private $mod;			# Unique identifier for current module
		private $cat;			# Category (variation of the current module)
		
		private $buffer;		# Stores raw data taken from the definition script
		
		private $name;
		private $plural;
		private $gender;
		
		private $table;
		private $auxTables;
		
		private $keys;
		private $frgnKeys;
		
		private $fields;
		
		/**
		 * Read definition file, pre-parse it and store results
		 * in local properties.
		 */
		public function __construct($mod, $cat){
			
			$this->mod = $mod;
			$this->cat = $cat;
			
			$src = $this->getSource()
				or die("Modules Parser: failed loading current module's definitions script");
			
			$buffer = array(
				'basics' => $basics,
			);
			
		}
		
		private function getSource(){
			
			$path = MODULES_DEFINITIONS."/{$mod}.def.php";
			$class = "Modules_Definitions_{$mod}";
			
			if( is_file($path) ) require_once( $path );
			if( !class_exists($class) ) return false;
			
			return new $class( $this->cat );
			
		}
		
	}

?>