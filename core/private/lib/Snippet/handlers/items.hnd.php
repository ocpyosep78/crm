<?php

	class Snippet_hnd_items extends Snippets_Handlers_Commons{
	
		protected function handle_viewItem(){
		
/* Array(
	[code] => customers
	[snippet] => viewItem
	[filters] => 129
	[writeTo] => 
	[modifier] => null
	[group_uID] => 0.64424800 1301574930
	[initialize] => 
) */
		
			$data = $this->Source->getData('item', $this->params['modifier']);
			
			# Form data blocks (for presentational purposes)
			$block = 0;
			$blocks = array();
			
			foreach( $this->fields as $field => $atts ){
				($field == '>') ?  $block++ : $blocks[$block][$field] = $atts;
			}
			test( $data );
			$this->assign('data', $data);
			$this->assign('blocks', $blocks);
		
			return $this->fetch( 'items/view' );
		
		}
	
		protected function handle_createItem(){

/* Array(
	[code] => customers
	[snippet] => createItem
	[writeTo] => 
	[modifier] => 
	[filters] => Array()
	[group_uID] => 0.28929300 1301575064
	[initialize] => 
) */

		}
	
		protected function handle_editItem(){

/* Array(
	[code] => customers
	[snippet] => editItem
	[filters] => 106
	[writeTo] => 
	[modifier] => null
	[group_uID] => 0.64424800 1301574930
	[initialize] => 
) */
		
			return $this->fetch( 'items/edit' );
		
		}
	
	}

?>