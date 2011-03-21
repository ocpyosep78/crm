<?php

	class Modules_ajaxEngine{
		
		public function write($element, $text){
		
			return addAssign($element, 'innerHTML', $text);
		
		}
		
	}

?>