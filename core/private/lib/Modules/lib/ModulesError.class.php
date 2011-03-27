<?php

	class ModulesError{
		
		/**
		 * Shortcut to return a generic error page
		 */
		public function fetch( $msg=NULL ){
			
			$TemplateEngine = new Modules_templateEngine;
			
			$TemplateEngine->assign('msg', $msg);
			
			return $TemplateEngine->fetch( 'error.tpl' );
			
		}
		
	}

?>