<?php

	class Snippets_Handler_Source extends Snippets_Handler_Defaults{

		protected $sqlEngine;

		private $warnings;

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

		public function isFrozen( $field ){

			return in_array($field, $this->summary['frozen']);

		}

		public function getBasics(){

			return $this->basics;

		}

		public function getSummary( $key=NULL ){

			return $key ? $this->summary[$key] : $this->summary;

		}

		public function getListFor( $field )
		{
			$lists = $this->summary['fieldsByType']['list'];

			if( !isset($lists[$field]) ) return array();

			# If a source is set (another module), get data from it
			if (!empty($lists[$field]['listSrc']))
			{
				$code = $lists[$field]['listSrc'];
				$params = $this->params;
				$Src = $this->Layers->get('modules')->getModule($code, $params);
				return $Src->getData('hash');
			}
			# Else, search for a method called listForField<fieldName>
			elseif( method_exists($this, $method="listForField{$field}") )
			{
				return $this->sqlEngine->query($this->$method(), 'col');
			}
			# Else add a warning
			else
			{
				Snippet_Tools::issueWarning("list {$field} not implemented");
			}
		}

		public function getFields( $dt )
		{
			# Take '>' as a valid field (it's for presentational purposes)
			$fieldsAtts = $this->summary['fieldsAtts'] + array('>' => NULL);

			# Take the right list of fields from definition file (or defaults)
			$setFields = $this->getFieldsFor( $dt );

			$fields = array_intersect_key(array_flip($setFields), $fieldsAtts);
			foreach( $fields as $field => &$atts ) $atts = $fieldsAtts[$field];

			return $fields;
		}

		public function getFieldsByType()
		{
			return $this->summary['fieldsByType'];
		}

		# $type: list, item, hash
		public function getData($type, $filters=array())
		{
			switch ($type)
			{
				case 'list':		# asList
					$format = 'named';
					$params = $this->getSummary('keys');
					$offset = ($this->params['page']-1) * 10;
					$sql = "{$this->getListData($filters)}
					        LIMIT {$offset}, 10";
					break;

				case 'hash':		# asHash
					$format = 'col';
					$params = array('key' => $this->getSummary('keysString'),
					                'val' => 'tipToolText');
					$sql = $this->getListData($filters);
					break;

				case 'item':		# query, type row
					$format = 'row';
					$params = NULL;
					$sql = $this->getItemData( $filters );
					break;
			}

			return is_string($sql)
				? $this->sqlEngine->query($sql, $format, $params)
				: (is_array($sql) ? $sql : false);

		}

		public function getRuleSet(){

			return $this->getValidationRuleSet();

		}

		public function prefetchInput( &$data ){

			# Passed by ref
			$this->prefetchUserInput( $data );

			return $this;

		}

		public function validateInput( $data ){

			$Valid = new Snippet_Validation;

			return $Valid->test($data, $this->getRuleSet());

		}


/**************************************************/
/******************* READ / SET *******************/
/**************************************************/

		/**
		 * Initiate required properties of the object
		 */
		public function inject($code, $params){

			$this->code = $code;
			$this->params = $params;

			return $this;

		}

		/**
		 * Aquire data and fill gaps in the definition file
		 */
		public function aquire()
		{
			# Basics (name, plural and tip)
			$this->basics = (array)$this->getBasicAttributes()
				+ array('name' => NULL, 'plural' => NULL, 'gender' => NULL, 'tip' => NULL);

			# 1. Get tables from database definition (@tables)
			# 2. Fill unset attributes with defaults
			# 3. Get tools set in definition and mix it with @toolsBase
			$this->aquireDB()->fillAttributes()->storeTools();

			# Build lists from tables (@hidden, @keys)
			$this->buildDBSummary();

			# Build the sql layer and feed it what we have aquired
			$this->sqlEngine->feed( $this->summary );

			return $this;
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
				Snippet_Tools::issueWarning('db structure definition is invalid');
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
					Snippet_Tools::issueWarning('db structure definition is empty or invalid');
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
					# Unless explicitly frozen, all fields are assumed to be edittable
					$atts['frozen'] = !empty($atts['frozen']);
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

            foreach( $btns as $btn ){
                if (is_array($btn)) {
                    $this->tools[end(array_keys($btn))] = array_shift($btn);
                } elseif (isset($this->toolsBase[$btn])) {
                    $this->tools[$btn] = $this->toolsBase[$btn];
                }
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
			$fieldsAtts = array();
			$shown = array();
			$frozen = array();
			$keys = array();
			$FKs = array();
			$tools = $this->tools;

			# Initialize $fieldsByType
			$fieldTypes = array('text', 'area', 'list', 'image', 'date',
				'time', 'datetime', 'option', 'options');
			foreach( $fieldTypes as $type ) $fieldsByType[$type] = array();

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
					# List of frozen (uneditable) fields
					!$atts['frozen'] || $frozen[] = $field;
					# List of foreign keys, by field (always from mainTable)
					!$atts['FK'] || $FKs[$field] = array(
						'table'		=> $code,
						'target'	=> $atts['FK'],
					);
					# Set text as default type and fill $fieldsByType array
					!empty($atts['type']) || $atts['type'] = 'text';
					$fieldsByType[$atts['type']][$field] = $atts;
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
				'fieldsByType'	=> $fieldsByType,
				'shown'			=> $shown,
				'frozen'		=> $frozen,
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

			if( empty($filters) ) die('not enough info to identify item to update');

			return $this->sqlEngine->update($data + $filters, $table, $fKeys);

		}

		public function insert( $data ){

			$table = array_shift($this->summary['mainTable']);

			return $table ? $this->sqlEngine->insert($data, $table) : false;

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
			return $this->sqlEngine->fixFilters($filters, $fix);
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


/* TEMP : preliminary sketch of a function to automatically create select queries (lists, combo, info, etc.) */
public function newSelect($type, $filters, $constraints){

# => $type IN ('list' [=> ALL], 'hash' [=> COMBO], 'item' [=> INFO])
# => $filters := array('field' => 'value', 'field' => 'value', 'field' => 'value')

	$sql[] = "SELECT";
# => `c`.`number`, `c`.`name`, `d`.`seller`
	$sql[] = $this->getQueryFields( $type );
	$sql[] = "FROM";
# => `customers` `c` INNER JOIN `customers_contacts` `cc` USING (`id_customer`) LEFT JOIN `_users` `u` ON (`u`.`user` = `c`.`seller`)
	$sql[] = $this->getQueryTables( $type );
	$sql[] = "WHERE";
# => `c`.`number` < 100 AND (`cc`.`name` LIKE '%jose%' OR ISNULL(`cc`.`name`)) AND `u`.`id_profile` <= 2
	$sql[] = $this->getQueryFilters( $filters );
# => GROUP BY `cc`.`number`, `u`.`name` ORDER BY `cc`.`name` LIMIT 18, 40
	$sql[] = $this->getQueryConstraints( $constraints );

	return join(' ', $sql);

}

	}