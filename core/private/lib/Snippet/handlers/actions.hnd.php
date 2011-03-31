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
		
/* Array(
	[code] => customers
	[snippet] => deleteItem
	[filters] => 129
	[writeTo] => 
	[modifier] => null
	[group_uID] => 0.64424800 1301574930
	[initialize] => 
) */

			return $this->handle_delete();
			
		}
	
		protected function handle_delete(){
		
			return 'delete';
		
		}
		
	}

?>