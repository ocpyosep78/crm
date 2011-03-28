<?php

	class ModulesActions extends ModulesBase{
		
		public function ajaxDo(){
			
			test(array(
				$this->type,
				$this->code,
				$this->modifier,
				$this->params,
			));
		
		}
		
	}
	
?>