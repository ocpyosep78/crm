<?php

	class Snippet_hnd_widgets extends Snippets_Handlers_Commons{
	
		protected function handle_bigTools(){
		
			return $this->fetch('widgets/bigTools');
			
		}
		
		/**
		 * Check that combo field is valid and keys are set
		 * If so, fetch its HTML and return it
		 */
		protected function handle_comboList(){
		
			# We need key(s) to present comboList
			if( empty($this->keys) ) return '';
		
			# Get configured field code
			$field = $this->DP->getComboListField();
			
			# Attempt to get data from its own function, or fall back to
			# common, then simpleList data
			$data = $this->getListData('combo');
			if( $data === NULL ){	# getComboListData was not set
				$extData = ($aux=$this->getListData('common'))
					? $aux
					: $this->getListData('simple');
			}
			
			# If we still have no data, then we cannot present comboList
			if( empty($data) && empty($extData) ) return '';
			
			# If we have external data, we have to translate it into comboList data
			# (while lists are multidimensional, a combo is just a dictionary)
			if( isset($extData) ){
				$sampleRow = array_shift($aux=$extData);
				# If we're lacking keys, there's no way to do it
				foreach( $this->keys as $k => $v ){
					if( !isset($sampleRow[$v]) ) return '';
				}
				# If our field does not exist in data, there's nothing to do either
				if( !isset($sampleRow[$field]) ) return '';
				# Now do translate the array
				foreach( $extData as $item ){
					# Support for multiple keys
					$keysString = $this->keysArray2String( $item );
					if( is_null($keysString) ) return '';
					$data[$keysString] = $item[$field];
				}
			}
			
			if( empty($data) ) return '';
			
			$this->assign('combo', array(
				'list'		=> $data,
				'selected'	=> $this->params,
			));
			
			return $this->fetch( 'widgets/comboList' );
			
		}
	
	}

?>