<?php

	class PageChecker{
	
		private function supportedTypes(){
		
			return array('commonList', 'simpleList', 'create', 'edit', 'info');
		
		}
		
		public function parsePageName( $page ){
		
			foreach( $this->supportedTypes() as $type ) $types[] = ucfirst($type);
			if( empty($types) ) return array(NULL, NULL);
			
			# See if the page can be created
			$str = join('|', $types);
			preg_match("/^(.+)({$str})$/", $page, $parts);
			
			# Uncapitalize first letter of the code, if we got one
			if( isset($parts[2]) ) $parts[2][0] = strtolower( $parts[2][0] );
			
			return array(
				empty($parts) ? NULL : $parts[2],	# type
				empty($parts) ? NULL : $parts[1],	# code
			);
			
		}
	
		public function pageNameFromCode($code, $type){
			
			return $code.ucfirst($type);
			
		}
	
		public function canBuildPage( $name ){
		
			list($type, $code) = $this->parsePageName( $name );
		
			return $this->canBuildPageFor($type, $code);
		
		}
		
		public function canBuildPageFor($type, $code){
			
			return in_array($type, $this->supportedTypes())
				&& is_file(MOD_DEFINITIONS_PATH."Mod_{$code}.mod.php");
		
		}

	}

?>