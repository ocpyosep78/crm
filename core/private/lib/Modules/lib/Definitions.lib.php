<?php

	class Modules_Definitions{
		
		protected $category;
		
		public function __construct( $category ){
			
			$this->category = $category;
			
		}
		
		public function getData(){
			
			$this->gatherInfo()->fillGaps();
			
			$propertiesToReturn = array('name', 'plural', 'gender',
				'tables', 'rules', 'listFields', 'itemFields',
				'creationFields', 'ruleSet');
			
			foreach( $propertiesToReturn as $prop ){
				$ret[$prop] = isset($this->$prop)
					? $this->$prop
					: NULL;
			}
			
			return $ret;
			
		}
		
		# Call all definition functions that are accessible
		private function gatherInfo(){
			
			$methodsToCall = array('basics', 'longIdentifier',
				'dbStructure', 'dbRules', 'listColumns',
				'itemRows', 'createItem', 'validation');
				
			foreach( $methodsToCall as $call ){
				!is_callable(array($this, $call)) || $this->$call();
			}
			
			return $this;
			
		}
	
		# Fill gaps with default values
		private function fillGaps(){
			
			$that = $this;
			$fill = function($property, $value){
				isset($that->$property) || $that->$property = $value;
			};
			
			$fill('name', 'Elemento');
			$fill('plural', 'Elementos');
			$fill('gender', 'male');
			$fill('tables', array());
			$fill('rules', array());
			$fill('listFields', array());
			$fill('itemFields', array());
			$fill('creationFields', array());
			$fill('ruleSet', array());
			
			return $this;
			
		}
		
		public function cb_handleFilters( $filters ){
			
			!is_callable(array($this, 'fixFilters'))
				|| $this->fixFilters( $filters );
					
		}
		
		public function cb_handleData( $data ){
			
			!is_callable(array($this, 'fixData'))
				|| $this->fixData( $data );
					
		}
		
	}