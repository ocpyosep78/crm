<?php

	class Snippet_hnd_items extends Snippets_Handlers_Commons{
	
		protected function handle_view(){
		
			$data = $this->Source->getData('item', $this->params['modifier']);
			
			# Form data blocks (for presentational purposes)
			$block = 0;
			$blocks = array();
			
			foreach( $this->fields as $field => $atts ){
				($field == '>') ?  $block++ : $blocks[$block][$field] = $atts;
			}
			
			$this->assign('data', $data);
			$this->assign('blocks', $blocks);
		
			return $this->fetch( 'items/view' );
		
		}
	
		protected function handle_edit(){
		
			return $this->fetch( 'items/edit' );
		
		}
	
	}

?>