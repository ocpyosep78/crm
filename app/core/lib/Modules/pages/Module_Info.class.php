<?php

	class Module_Info extends ModulesBase{
	
		public function getPage(){
			
			# Make sure the type of page is valid
			if( !method_exists($this, $this->type) ) return $this->displayError('Module_Info: wrong type.');
			
			if( !$this->setDataProvider() ) return $this->displayError('Module_Info: DP not found.');
			
			# Get static data and register it for the template engine to access it.
			$cfgIntegrity = $this->readConfig();
			if( $cfgIntegrity !== true ) return $this->displayError( $cfgIntegrity );
			
			# Combo list
			$this->insertComboList();
			
			# Let the right method handle the rest, depending the list type
			return $this->{$this->type}();
			
		}
		
		private function info(){
			
			$this->assign('data', array('Nombre' => 'yo', 'Edad' => '32'));
			
			return $this->fetch( 'info' );
			
		}
	
	}

?>