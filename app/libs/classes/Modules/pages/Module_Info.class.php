<?php

	class Module_Info extends ModulesBase{
	
		public function getPage( $page ){
		
			switch( $page ){
				case 'info': return $this->infoPage();
				default: return '';
			}
			
		}
		
		private function infoPage(){
		
			$this->assign('comboList', oLists()->comboListHTML($this->code, $this->modifier));
			$this->assign('data', array('Nombre' => 'yo', 'Edad' => '32'));
			
			return $this->fetch( 'info' );
			
		}
	
	}

?>