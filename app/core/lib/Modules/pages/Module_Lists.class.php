<?php

	class Module_Lists extends ModulesBase{
	
		public function getPage(){
			
			# Make sure the type of page is valid
			if( !method_exists($this, $this->type) ) return $this->displayError('Module_Lists: wrong type.');
			
			if( !$this->setDataProvider() ) return $this->displayError('Module_Lists: DP not found.');
			
			# Get static data and register it for the template engine to access it.
			$cfgIntegrity = $this->readConfig();
			if( $cfgIntegrity !== true ) return $this->displayError( $cfgIntegrity );
			
			# Combo list
			$this->insertComboList();
			
			# Let the right method handle the rest, depending the list type
			return $this->{$this->type}();
			
		}
		
		private function commonList(){
		
			return $this->fetch( 'lists_frame' );
		
		}
		
		private function simpleList(){
		
			return $this->fetch( 'lists_simple' );
			
		}
		
		public function doTasks(){
		
			addScript( "initializeList('{$this->code}', '{$this->modifier}');" );
			
		}
	
	}

?>