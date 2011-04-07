<?php

	class SnippetLayer_access{
	
		public function can($snippet, $code){
			
			switch( $snippet ){
				case 'list':
				case 'commonList':
				case 'innerCommonList':
				case 'simpleList':
					$what = $code;
					break;
				case 'createItem':
				case 'editItem':
				case 'deleteItem':
				case 'create':
				case 'edit':
				case 'delete':
				case 'block':
				case 'unblock':
					$what = preg_replace('/Item$/', '', $snippet).ucfirst($code);
					break;
				case 'view':
				case 'viewItem':
					$what = $code.'Info';
					break;
				default:
					return false;
			}
		
			return oPermits()->can( $what );
		
		}
	
		public function cant($snippet, $code){
		
			return !$this->can($snippet, $code);
		
		}
		
	}

?>