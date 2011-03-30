<?php
 
  	require_once(dirname(__FILE__).'/Handler_Defaults.class.php');
	
	class Snippets_Handler_Interpreter extends Snippets_Handler_Defaults{
	
		private $warnings;
	
		private $snippet;
		private $code;
		private $params;
		
		private $basics;
		
		private $db;
		private $summary;
		
		
		public function __construct(){
			
			$this->Layers = new Snippet_Layers;
		
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
			$this->aquireDB();
			
			# Fill unset attributes with defaults
			$this->fillAttributes();
			
			# Build lists from tables (@hidden, @keys)
			$this->buildDBSummary();
			
			# Build the sql layer and feed it what we have aquired
			$this->sqlEngine = $this->Layers->get( SNIPPETS_SQL_ENGINE );
			$this->sqlEngine->feed( $this->summary );
		
		}
	
		/**
		 * Validate (in general) the integrity of the defintions
		 */
		public function validate(){
		
			
		
		}
		
		
/*****************************************/
/**************** PRIVATE ****************/
/*****************************************/
		
		/**
		 * Read and validate input from definition file, for database
		 */
		private function aquireDB(){
			
			# Read and validate db definition, on failure leave it empty
			$db = $this->getDatabaseDefinition();
			if( !is_array($db) ){
				# Keep defaults when definition fails
				$db = array();
				$this->raise('db structure definition is invalid');
			}
			else{
				$correct = true;
				foreach( $db as $table => $content ){
					$db[$table] = $content;
					$correct = $correct & is_array($content) & !empty($content);
				}
				if( !$correct || empty($db) ){
					# Keep defaults when definition fails
					$db = array();
					$this->raise('db structure definition is empty or invalid');
				}
			}
			
			$this->db = $db;
			
		}
		
		/**
		 * Fix ambiguous entries and fill unset keys in tables definition
		 */
		private function fillAttributes(){
		
			# Fill unset attributes with defaults
			foreach( $this->db as $table => &$content ){
				foreach( $content as $field => &$atts ){
					# Accept strings as attribute, take it as name
					!is_string($atts) || $atts = array('name' => $atts);
					# Set default type as text
					empty($atts['type']) && $atts['type'] = 'text';
					# Set isKey fields where not set
					$atts['isKey'] = !empty($atts['isKey']);
					# Hidden default is true for keys, false for all others
					!isset($atts['hidden']) && $atts['hidden'] = !!$atts['isKey'];
					# Set FK where not set
					!isset($atts['FK']) && $atts['FK'] = NULL;
				}
			}
			
		}
		
		/**
		 * Export relevant data to more accessible lists (keys, fks, etc.)
		 */
		private function buildDBSummary(){
		
			$db = $this->db;
			
			$mainTable = NULL;
			$tables = array();
			$fields = array();
			$shown = array();
			$keys = array();
			$FKs = array();
			
			foreach( $db as $table => $content ){
				# Store tables as `code => name` pairs
				$code = $this->buildUniqueTableCode($table, $tables);
				# Main table is the first table given, by convention
				!$mainTable
					? $mainTable[$code] = $table
					: $tables[$code] = $table;
				# Go through all fields and record them as list
				foreach( $content as $field => $atts ){
					$fullID = "{$code}.{$field}";
					$fields[] = $fullID;
					if( $atts['isKey'] && $mainTable == $code ){
						$keys[] = $field;
					}
					$atts['hidden'] || $shown[] = $fullID;
					!$atts['FK'] || $FKs[$field] = array(
						'table'		=> $code,
						'target'	=> $atts['FK'],
					);
				}
			}
			
//			$forLists = (array)$this->getListFields();
//			$fieldsFor['lists'] = array_intersect_keys($forLists, $fields);
			
			$this->summary = array(
				'mainTable' => $mainTable,
				'tables'	=> $tables,
				'fields'	=> $fields,
				'shown'		=> $shown,
				'keys'		=> $keys,
				'FKs'		=> $FKs,
			);
			
		}
		
		/*
		 * Find a unique shortname for each table
		 */
		private function buildUniqueTableCode($table, $tables){
			
			# Take one more letter in each round untill unique
			$code = $table[0];
			while( isset($tables[$code]) && $code != $table ){
				$code = substr($table, 0, strlen($code)+1);
			}
			
			# If by any chance complete table name is in use (could
			# happen in a very particular scenario), we give up and
			# assing the previous table its full name as code (which
			# is certainly not in use, because any prior code would
			# be shorter not larger, and it can't be same as current
			# table's either)
			!isset($tables[$code])
				|| $table[$table[$code]] = $table[$code];
			
			return $code;
			
		}
		
		
		private function raise( $msg ){
			$this->test( $msg );				/* TEMP */
		}
		public function test( $var=NULL ){
			
			$var = $this->sqlEngine->generate('list');
			
			return '<pre>'.var_export($var, true).'</pre>';
			
		}
	
	}

?>