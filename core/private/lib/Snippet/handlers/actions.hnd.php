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
	
		protected function handle_delete(){
		
			return 'delete';
		
		}
		
	}

?>