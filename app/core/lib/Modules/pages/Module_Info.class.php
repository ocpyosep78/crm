<?php

	class Module_Info extends ModulesBase{
	
		public function getPage( $filters=array() ){
		
			if( $this->foundError() ) return $this->foundError();
			
			# Let the right method handle the rest, depending the list type
			return $this->{$this->type}( $filters );
			
		}
		
		private function info( $filters=array() ){
			
			# Retrieve item's data
			$data = $this->getInfoPageData( $filters );
			if( empty($data) ) return $this->displayError('Module_Info error: No data found for this item');
			
			# Form data blocks (for presentational purposes)
			$block = 0;
			$blocks = array();
			foreach( $this->fields as $field ){
				if( $field == '>' ) $block++;
				else $blocks[$block][] = $field;
			}
			
			$this->assign('data', $data);
			$this->assign('blocks', $blocks);
			
			# Combo list
			$combo = $this->getComboList( $this->keysArray2String($filters) );
			
			return $combo.$this->fetch( 'info' );
			
		}
	
	}

?>