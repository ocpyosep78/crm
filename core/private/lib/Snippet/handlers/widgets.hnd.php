<?php

	class Snippet_hnd_widgets extends Snippets_Handlers_Commons{
	
		protected function handle_bigTools(){
		
			return $this->fetch('widgets/bigTools');
			
		}
		
		/**
		 * Check that combo field is valid and keys are set
		 * If so, fetch its HTML and return it
		 */
		protected function handle_comboList(){
			
			$data = $this->Source->getData('hash');
			$this->assign('combo', array(
				'list'		=> $data,
				'selected'	=> $this->params['modifier'],
			));
			
			return $this->fetch( 'widgets/comboList' );
			
		}
	
	}

?>