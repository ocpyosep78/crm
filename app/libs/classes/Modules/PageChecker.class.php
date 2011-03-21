<?php
	
	require_once( CLASSES_PATH.'/Modules/Modules.class.php' );
	

	class PageChecker{
	
		private function supportedTypes(){
		
			return array(	'commonList', 'simpleList', 'comboList',
							'create', 'edit', 'info');
		
		}
		
		public function parsePageName( $page ){
		
			foreach( $this->supportedTypes() as $type ) $types[] = ucfirst($type);
			if( empty($types) ) return array('code' => NULL, 'type' => NULL);
			
			# See if the page can be created
			$str = join('|', $types);
			preg_match("/^(.+)({$str})$/", $page, $parts);
			return array(
				'code'	=> empty($parts) ? NULL : $parts[1],
				'type'	=> empty($parts) ? NULL : strtolower($parts[2]),
			);
			
		}
	
		public function pageNameFromCode($code, $type){
			
			return $code.ucfirst($type);
			
		}
	
		public function canBuildPage( $page ){
			
			$parts = $this->parsePageName( $page );
		
			return $this->canBuildPageFor($parts['code'], $parts['type']);
		
		}
		
		public function canBuildPageFor($code, $type){
			
			return in_array($type, $this->supportedTypes())
				&& is_file(MODULES_PATH."{$code}.class.php");
		
		}

	}

?>