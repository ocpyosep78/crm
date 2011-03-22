<?php

	class Modules_templateEngine{
		
		private $engine;
	
		public function __construct(){
		
			$path = CORE_PATH.'third-party/Smarty-3.0.7/';
		
			require_once( "{$path}Smarty.class.php" );
			
			$Smarty = new Smarty;
			$Smarty->setTemplateDir(MODULES_TEMPLATES_PATH);
			$Smarty->setCompileDir('temp');
			$Smarty->setCacheDir(SMARTY_DIR.'cache');
			$Smarty->setConfigDir(SMARTY_DIR.'configs');
			
			$this->engine = $Smarty;
		
		}
		
		public function assign($var, $val){
		
			return $this->engine->assign($var, $val);
		
		}
		
		public function fetch( $template ){
		
			return $this->engine->fetch( $template );
		
		}
		
	}

?>