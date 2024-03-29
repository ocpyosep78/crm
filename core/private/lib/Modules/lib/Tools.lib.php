<?php

	class Snippet_Tools{
	
		public static function toJson( $arr=array() ){
			
			return json_encode($arr, JSON_HEX_APOS + JSON_FORCE_OBJECT);
		
		}
		
		public static function issueWarning( $msg=NULL ){
		
			$Layers = new Snippet_Layers;
			
			switch( SNIPPET_ERROR_OUTPUT ){
				case 'status':
					# Output error through ajax layer's #display method
					$Layers->get('ajax')->display($msg, 'error');
					return '';
				case 'html':
					# Output error through the returned HTML string
					$msg || $msg = 'Snippets: an unexpected error was raised';
					return "<div class='noResMsg'>{$msg}</div>";
				default:
					return '';
			}
			
		}
	
	}