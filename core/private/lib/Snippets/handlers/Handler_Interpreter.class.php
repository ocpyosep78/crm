<?php
 
  	require_once(dirname(__FILE__).'/Handler_Defaults.class.php');
	
	class Snippets_Handler_Interpreter extends Snippets_Handler_Defaults{
	
		private $warnings;
	
		private $snippet;
		private $code;
		private $params;
		
		private $basics;
		private $advanced;
		
		private $tables;
		private $fields;
		private $keys;
		
		
		public function __construct(){
		
			$this->warnings = array();
			
		}
	
		/**
		 * Initiate required properties of the object
		 */
		public function inject($snippet, $code, $params){
			
			$this->snippet = $snippet;
			$this->code = $code;
			$this->params = $params;
			
		}
	
		/**
		 * Aquire data and fill gaps in the definition file
		 */
		public function aquire(){
		
			# Basics (name, plural and tip)
			$this->basics = (array)$this->getBasicAttributes()
				+ array('name' => NULL, 'plural' => NULL, 'tip' => NULL);
			
			# Get tables from database definition (@tables)
			$this->aquireTables();
			
			# Fill unset attributes with defaults
			$this->fillTableAttributes();
			
			# Build lists from tables (@hidden, @keys)
			$this->parseTables();
		
		}
	
		/**
		 * Validate (in general) the integrity of the defintions
		 */
		public function validate(){
		
			
		
		}
		
		
/*****************************************/
/**************** PRIVATE ****************/
/*****************************************/
		
		private function aquireTables(){
			
			# Read and validate db definition, on failure leave it empty
			$db = $this->getDatabaseDefinition();
			if( !is_array($db) ){
				# Keep defaults when definition fails
				$tables = array();
				$this->raise('db structure definition is invalid');
			}
			else{
				$correct = true;
				foreach( $db as $table => $content ){
					$tables[$table] = $content;
					$correct = $correct & is_array($content) & !empty($content);
				}
				if( !$correct || empty($tables) ){
					# Keep defaults when definition fails
					$tables = array();
					$this->raise('db structure definition is empty or invalid');
				}
			}
			
			$this->tables = $tables;
			
		}
		
		private function fillTableAttributes(){
		
			# Fill unset attributes with defaults
			foreach( $this->tables as $table => &$content ){
				foreach( $content as $field => &$atts ){
					# Accept strings as attribute, take it as name
					!is_string($atts) || $atts = array('name' => $atts);
					# Set default type as text
					empty($atts['type']) && $atts['type'] = 'text';
					# Set isKey fields where unset
					$atts['isKey'] = !empty($atts['isKey']);
					# Hidden default is true for keys, false for all others
					!isset($atts['hidden']) && $atts['hidden'] = !!$atts['isKey'];
					
					empty($atts['isKey']) && $atts['isKey'] = false;
				}
			}
			
		}
		
		private function parseTables(){
		
			$tables = $this->tables;
			$hidden = array();
			$keys = array();
			
			foreach( $tables as $tables ){
				if( 
			}
			
		}
		
		private function raise( $msg ){
		
			test( $msg );				/* TEMP */
		
		}
	
	}

?>