<?php

	class Module_Lists extends ModulesBase{
	
		public function getPage( $filters=array() ){
		
			# commonListUpdate retrieves fields and other data as 'commonList',
			# but it needs to call its own handler #commonListUpdate
			if( $this->foundError() ) return $this->foundError();
			
			# Let the right method handle the rest, depending the list type
			return $this->{$this->type}( $filters );
			
		}
		
		protected function commonList( $params=array() ){
		
			# $filters holds uID, src and filters for updating commonList, but it's empty
			# for the first call (when it only wants to draw the list frame).
			if( empty($params['uID']) ) return $this->getComboList().$this->fetch( 'lists_frame' );
			
			# If we're still on the run, then we're expected to update the list
			
			# Move input to more comfortable vars
			$uID = $params['uID'];
			$filters = $params['filters'];
			$src = $params['src'];
			
			# Get Data
			$data = $this->getListData($src ? $src : 'common', $filters);
			$this->TemplateEngine->assign('data', $data);
			
/*			oSmarty()->assign('params', $static['params']);
			oSmarty()->assign('fields', $static['fields']);
			oSmarty()->assign('data', isset($data) ? $data : array());
			oSmarty()->assign('axns', $static['actions']);
			oSmarty()->assign('tools', $static['tools']);
			oSmarty()->assign('infoPage', "{$this->code}Info");
			
			addAssign('TableSearchCache', 'innerHTML', $this->template('list'));
			addScript("TableSearch.showResults('{$uID}');");
			
			return addScript("\$('listWrapper').update();"); */
			
			return $this->fetch( 'lists_common' );
		
		}
		
		protected function simpleList(){
		
			return $this->fetch( 'lists_simple' );
			
		}
		
		/**
		 * Check that combo field is valid and keys are set
		 * If so, fetch its HTML and return it
		 */
		protected function comboList( $selected=NULL ){
		
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
				'code'		=> $this->code,
				'params'	=> array('name' => $this->DP->getName()),
				'list'		=> $data,
				'selected'	=> $selected,
			));
			
			return $this->fetch( 'lists_combo' );
			
		}
		
		public function doTasks( $filters=array() ){
		
			$cmd = "Modules.initialize('{$this->type}', '{$this->code}', '{$this->modifier}');";
			
			if( isset($filters['uID']) ){
				$cmd .= "Modules.columnSearch.showResults('{$filters['uID']}');";
				$cmd .= "\$('listWrapper').update();";
			}
			elseif( $this->type !== 'comboList' ){
				$cmd .= "Modules.initialize('comboList', '{$this->code}', '{$this->modifier}');";
			}
			
			return $this->AjaxEngine->addScript( $cmd );
			
		}
	
	}

?>