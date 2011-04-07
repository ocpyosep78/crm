<?php

	class Snippet_hnd_actions extends Snippets_Handlers_Commons{
	
		protected function handle_edit(){
		
			return 'edit';
		
		}
	
		protected function handle_block(){
		
			return 'block';
		
		}
	
		protected function handle_unblock(){
		
			return 'unblock';
		
		}
	
		/* Alias of handle_delete */
		protected function handle_deleteItem(){

			return $this->handle_delete();
			
		}
	
		protected function handle_delete(){
		
			$ans = $this->Source->remove((array)$this->params['filters']);
			
			if( $ans->error ) $this->Layers->get('ajax')->display($ans->msg);
			else $this->Layers->get('ajax')->addReload($ans->msg, 1);
			
			/* BUG : when reloading the page (on success) commonList is not loaded
			   but other snippets are (bigTools, comboList) */
		
		}
		
	}

?>