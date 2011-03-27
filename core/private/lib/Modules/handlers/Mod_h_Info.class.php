<?php

	class Mod_h_Info extends ModulesBase{
		
		protected function info(){
		
			# Retrieve item's data
			$data = $this->getInfoPageData( $this->params );
			if( empty($data) ){
				return $this->Error( 'Module_Info error: No data found for this item' );
			}
			
			# Form data blocks (for presentational purposes)
			$block = 0;
			$blocks = array();
			foreach( $this->fields as $field ){
				if( $field == '>' ) $block++;
				else $blocks[$block][] = $field;
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
		private function getInfoPageData( $keys ){
		
			$filters = $this->sanitizeInfoKeys( $keys );
			
			# Get the right SQL, falling back to lists data if needed
			$sql = $this->DP->getInfoPageData( $filters );
			if( !$sql ) $altSQL = $sql = $this->DP->getCommonListData();
			if( !$sql ) $altSQL = $sql = $this->DP->getSimpleListData();
			if( !$sql ) return NULL;
			
			# Clear ORDER BY, GROUP BY and LIMIT clauses, and strip linefeeds
			# This is necessary only if we got the query from another page's
			if( !empty($altSQL) ){
				$sql = preg_replace('/\s/', ' ', $sql);
				$sql = preg_replace('/(GROUP BY|ORDER BY|LIMIT).+$/', '', $sql);
				$sql .= ( !strstr(strtoupper($sql), 'WHERE ') ? 'WHERE ' : 'AND ' ).
					" {$this->array2filter($filters)} LIMIT 1";
			}
			
			return $this->query($sql, 'row');
			
		}
	
	}

?>