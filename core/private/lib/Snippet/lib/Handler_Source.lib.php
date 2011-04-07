<?php
	
	class Snippets_Handler_Source extends Snippets_Handler_Defaults{
	
		private $sqlEngine;
		
		private $warnings;
	
		protected $snippet;
		protected $code;
		protected $params;
		
		private $basics;
		
		private $db;
		private $summary;
		
		# Available tools `code => screenName`
		private $toolsBase = array(
			'list'		=> 'listado',
			'create'	=> 'agregar',
			'view'		=> 'ver información de',
			'edit'		=> 'editar',
			'block'		=> 'bloquear',
			'unblock'	=> 'desbloquear',
			'delete'	=> 'eliminar',
		);
		
		
		public function __construct(){
			
			$this->Layers = new Snippet_Layers;
			
			$this->sqlEngine = $this->Layers->get( SNIPPETS_SQL_ENGINE );
			
			$this->Access = $this->Layers->get( 'access' );
		
			$this->warnings = array();
			
		}
		
		
/**************************************************/
/********************** GET ***********************/
/**************************************************/
		
		public function getBasics(){
		
			return $this->basics;
			
		}
		
		public function getSummary( $key=NULL ){
		
			return $key ? $this->summary[$key] : $this->summary;
			
		}
		
		public function getFieldsWithAtts( $type='lists' ){
		
			# Take '>' as a valid field (it's for presentational purposes)
			$fieldsAtts = $this->summary['fieldsAtts'] + array('>' => NULL);
			
			# Take the list of fields from either lists or items method
			$setFields = ($type == 'items')
				? $this->getItemFields()
				: $this->getListFields();
				
			$fields = array_intersect_key(array_flip($setFields), $fieldsAtts);
			foreach( $fields as $field => &$atts ) $atts = $fieldsAtts[$field];
		
			return $fields;
		
		}
		
		# $type: list, item, hash
		public function getData($type, $filters=array()){
		
			switch( $type ){
				case 'list':		# asList
					$format = 'named';
					$params = $this->getSummary('keys');
					$sql = $this->getListData( $filters );
					break;
				case 'hash':		# asHash
					$format = 'col';
					$params = array(
						'key' => $this->getSummary('keysString'),
						'val' => 'tipToolText',
					);
					$sql = $this->getListData( $filters );
					break;
				case 'item':		# query, type row
					$format = 'row';
					$params = NULL;
					$sql = $this->getItemData( $filters );
					break;
			}
			
			return $this->sqlEngine->query($sql, $format, $params);
			
		}
		
		
/**************************************************/
/******************* READ / SET *******************/
/**************************************************/

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
			
			# 1. Get tables from database definition (@tables)
			# 2. Fill unset attributes with defaults
			# 3. Get tools set in definition and mix it with @toolsBase
			$this->aquireDB()->fillAttributes()->storeTools();
			
			# Build lists from tables (@hidden, @keys)
			$this->buildDBSummary();
			
			# Build the sql layer and feed it what we have aquired
			$this->sqlEngine->feed( $this->summary );
		
		}
		
		/**
		 * Read and validate input from definition file, for database
		 */
		private function aquireDB(){
			
			# Read and validate db definition, on failure leave it empty
			$db = $this->getDatabaseDefinition();
			if( !is_array($db) ){
				# Keep defaults when definition fails
				$db = array();
				$this->issueWarning('db structure definition is invalid');
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
					$this->issueWarning('db structure definition is empty or invalid');
				}
			}
			
			$this->db = $db;
			
			return $this;
			
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
			
			return $this;
			
		}
		
		private function storeTools(){
		
			# Get all available tools
			$base = $this->toolsBase;
			
			# Always include list button in bigTools
			$btns = array_merge((array)'list', (array)$this->getTools());
			
			# Extend $base with other attributes to build final tools
			$this->tools = array();
			foreach( $base as $id => &$axn ){
				!in_array($id, $btns) || $this->tools[$id] = $axn;
			}
			
			return $this;
			
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
			$tools = $this->tools;
			
			foreach( $db as $table => $content ){
				# Store tables as `code => name` pairs
				$code = $this->buildUniqueTableCode($table, $tables);
				# Main table is the first table given, by convention
				!$mainTable
					? $mainTable[$code] = $table
					: $tables[$code] = $table;
				# Go through all fields and record them as list
				foreach( $content as $field => $atts ){
					# Field full name (formatted as tableCode.fieldName)
					$fullID = "{$code}.{$field}";
					# List of fields in one dimension, by fullID
					$fields[] = $fullID;
					# List of fields with their atts, by field (first kept)
					isset($fieldsAtts[$field]) || $fieldsAtts[$field] = $atts;
					# List of shown fields, by fullID
					$atts['hidden'] || $shown[] = $fullID;
					# List of key fields, by field name
					if( $atts['isKey'] && isset($mainTable[$code])  ){
						$keys[] = $field;
					}
					# List of foreign keys, by field (always from mainTable)
					!$atts['FK'] || $FKs[$field] = array(
						'table'		=> $code,
						'target'	=> $atts['FK'],
					);
				}
			}
			
			# Tools are enabled by default
			foreach( $tools as $k => &$v ){
				$v = array(
					'name'		=> $v,
					'disabled'	=> $this->Access->cant($k, $this->code),
				);
			}
			
			$this->summary = array(
				'mainTable' 	=> $mainTable,
				'tables'		=> $tables,
				'fields'		=> $fields,
				'fieldsAtts'	=> $fieldsAtts,
				'shown'			=> $shown,
				'keys'			=> $keys,
				'keysString'	=> join('__|__', $keys),
				'FKs'			=> $FKs,
				'tools'			=> $tools,
			);
			
			return $this;
			
		}
		
		
/**************************************************/
/****************** MODIFY ITEMS ******************/
/**************************************************/

		public function delete( $vKeys ){
		
			$vKeys = (array)$vKeys;
		
			$fKeys = $this->summary['keys'];
			$table = array_shift($this->summary['mainTable']);
			
			if( count($fKeys) == 1 && count($vKeys) == 1 ){
				$filters = array_combine($fKeys, $vKeys);
			}
			else{
				die( 'not implemented' );		/* TEMP */
			}
			
			if( empty($filters) ) die('not enough filters set for removal');
			
			return $this->sqlEngine->delete($table, $filters);
		
		}

		public function update($vKeys, $data){
		
			$vKeys = (array)$vKeys;
		
			$fKeys = $this->summary['keys'];
			$table = array_shift($this->summary['mainTable']);
			
			if( count($fKeys) == 1 && count($vKeys) == 1 ){
				$filters = array_combine($fKeys, $vKeys);
			}
			else{
				die( 'not implemented' );		/* TEMP */
			}
			
			if( empty($filters) ) die('not enough filters set for removal');
			
			return $this->sqlEngine->update($data + $filters, $table, $fKeys);
		
		}
		
		
/**************************************************/
/********************* TOOLS **********************/
/**************************************************/
		
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
		
		protected function fixFilters(&$filters, $fix){
			return $this->sqlEngine->fixFilters(&$filters, $fix);
		}
		protected function array2filter($arr, $joint='AND', $compare='LIKE'){
			return $this->sqlEngine->array2filter($arr, $joint, $compare);
		}
		
		
/**************************************************/
/***************** ERROR HANDLING *****************/
/**************************************************/
	
		/**
		 * Validate (in general) the integrity of the defintions
		 */
		public function validate(){
		
			return true;
		
		}
	
	}

?>