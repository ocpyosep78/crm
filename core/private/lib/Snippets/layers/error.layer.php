<?php

	class SnippetLayer_error{
	
		public function Error( $msg=NULL ){
		
			isset($msg) || $msg = 'Snippets: an unexpected error was raised while retrieving a page';
		
			return "<div class='noResMsg'>{$msg}</div>";
		
		}
	
	}

?>