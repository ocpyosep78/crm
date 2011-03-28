<?php

	class Mod_h_Info extends ModulesBase{
		
		/**
		 * @overview: builds the HTML for an infoPage, that is a page
		 *            where all relevant about an item is shown
		 * @returns: an HTML string
		 * @notes: $params wildcard ($this->params) is, for these pages,
		 *         either a string representing the key(s) to the item,
		 *         or an array with all keys as its elements.
		 */
		protected function info(){
		
			# Retrieve item's data
			if( empty($this->params) ){
				return $this->recordError( 'Module_Info error: No item id was received' );
			}
			$data = $this->getInfoPageData();
			
			# If getting data raised errors, return soon;
			if( $this->recordedError ) return;
			
			# Validate retrieved data (NULL- not defined/corrupt, FALSE- sql error)
			if( $data === false ){
				return $this->recordError( 'Module_Info error: An error occurred attempting to load data' );
			}
			if( is_null($data) ){
				return $this->recordError( 'Module_Info error: This page is not defined' );
			}
			if( empty($data) ){
				return $this->recordError( 'Module_Info error: No data found for this item' );
			}
			
			# Form data blocks (for presentational purposes)
			$block = 0;
			$blocks = array();
			foreach( $this->fields as $field => $atts ){
				($field == '>') ?  $block++ : $blocks[$block][$field] = $atts;
			}
			
			$this->assign('data', $data);
			$this->assign('blocks', $blocks);
			
			return $this->fetch( 'pages/info' );
			
		}
		
		/**
		 * @overview: Gets a data array from user-configured sql query for
		 *            infoPage (alternatively, it looks for list queries if
		 *            infoPage query is not defined, adding filter by keys)
		 * @returns: - on success, an array (Connection's row type)
		 *           - on sql error, false
		 *           - if missing, NULL
		 */
		private function getInfoPageData(){
			
			# Get filters for this item (keys and their values)
			$filters = $this->getFilterKeys();
			if( $this->recordedError ) return;
			
			# Get the defined SQL
			$sql = $this->DP->getInfoPageData( $filters );
			
			# Fall back to lists data queries if info-specific query is not defined
			if( !$sql ) $altSQL = $sql = $this->DP->getCommonListData();
			if( !$sql ) $altSQL = $sql = $this->DP->getSimpleListData();
			if( !$sql ){
				return $this->recordError('Mod_h_Info error: this page is not available');
			}
			
			# Clear ORDER BY, GROUP BY and LIMIT clauses, and strip linefeeds
			# This is necessary only if we got the query from a list (altSQL set)
			if( !empty($altSQL) ){
				$sql = preg_replace('/\s/', ' ', $altSQL);
				$sql = preg_replace('/(GROUP BY|ORDER BY|LIMIT).+$/', '', $sql);
				$sql .= ( !strstr(strtoupper($sql), 'WHERE ') ? 'WHERE ' : 'AND ' ).
					" {$this->array2filter($filters)} LIMIT 1";
			}
			
			return $this->query($sql, 'row');
			
		}
		
		/**
		 * @overview: keys are passed as either a string or an array, but always
		 *            using @params. This method processes whatever @params is and
		 *            attempts to return a valid filter to get this item's data
		 * @returns: an associative array with `keyField => fieldValue` pairs
		 */
		private function getFilterKeys(){
		
			$expected = $this->keys;
			$received = $this->params;
			
			if( empty($received) ){
				return $this->recordError('Mod_h_Info error: no id received for this item');
			}
			
			# As string, not compounded, and we have only one key and only one
			if( is_string($received) && !strstr($received, '__|__') && count($expected) == 1 ){
				return array_combine($expected, (array)$received);
			}
			
			# As string, general: parse and validate amount (contents validated below)
			if( is_string($received) ){
				$pairs = explode('__|__', $received);
				foreach( $pairs as $pair ){
					# Keys shall not include ':' but values might, so explode up to 2 parts only
					$key = explode(':', $pair, 2);
					if( count($key) != 2 ){
						return $this->recordError('Mod_h_Info error: badly formatted keys received');
					}
					$received[$key[0]] = $key[1];
				}
				if( !is_array($received) or empty($received) ){
					return $this->recordError('Mod_h_Info error: badly formatted keys received');
				}
			}
			
			# Verify we got all keys we expected and no others
			if( array_intersect_key($received, array_fill_keys($expected, NULL)) != $received ){
				return $this->recordError('Mod_h_Info error: received keys don\'t match expected keys');
			}
			
			return $received;
			
		}
	
	}

?>