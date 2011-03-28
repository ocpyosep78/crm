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
			$filters = $this->keysString2Array();
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
	
	}

?>