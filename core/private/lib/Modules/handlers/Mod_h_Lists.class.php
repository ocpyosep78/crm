<?php
	
	/**
	 * 
		 *            (notice this class descends from ModulesBase and has
		 *            access to its properties $type, $code and $modifier)
	 */

	class Module_Handler_Lists extends ModulesBase{
		
		/**
		 * @overview: commonList at first just prints the frame in which the
		 *            actual list will be loaded, and its initialize function
		 *            will request the list to be loaded (innerCommonList)
		 *            from the client, as soon as the frame is ready.
		 */
		protected function commonList(){
		
			return $this->fetch( 'lists/commonList' );
			
		}
		
		/**
		 * @overview: this is the actual list that goes within a commonList.
		 *            There's nothing against calling it on its own as any
		 *            regular element (to present it without the frame or in
		 *            another frame, maybe)
		 */
		protected function innerCommonList(){
			
			# Get Data
			$src = !empty($this->params['src']) ? $this->params['src'] : 'common';
			$data = $this->getListData($src, $this->params['filters']);
			$this->TemplateEngine->assign('data', $data);
			
/*			oSmarty()->assign('axns', $static['actions']);
			oSmarty()->assign('tools', $static['tools']); */
			
			return $this->fetch( 'lists/innerCommonList' );
		
		}
		
		protected function simpleList(){
		
			return $this->fetch( 'lists/simpleList' );
			
		}
		
		/**
		 * Check that combo field is valid and keys are set
		 * If so, fetch its HTML and return it
		 */
		protected function comboList(){
		
			# We need key(s) to present comboList
			if( empty($this->keys) ) return '';
		
			# Get configured field code
			$field = $this->DP->getComboListField();
			
			# Attempt to get data from its own function, or fall back to
			# cached listData, or fall back to common, then simpleList
			$data = $this->getListData('combo');
			if( $data === NULL ){	# getComboListData was not set
				if( is_null($extData=$this->getDataCache('common')) ){
					$extData = ($aux=$this->getListData('common'))
						? $aux
						: $this->getListData('simple');
				}
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
			
			return $this->fetch( 'lists/comboList' );
			
		}
		

##################################
############ PRIVATE #############
##################################
		
		/**
		 * @overview: Gets a data array from user-configured sql query
		 * @returns: - on success, an array
		 *           - on sql error, false
		 *           - if missing, NULL
		 */
		private function getListData($listCode, $filters=array()){
		
			$method = 'get'.ucfirst($listCode).'ListData';
			$formatAs = ($listCode == 'combo') ? 'asHash' : 'asList';
			
			$sql = $this->DP->$method( $filters );
			
			return $sql ? $this->$formatAs($sql, $this->keys) : NULL;
			
		}
	
	}

?>