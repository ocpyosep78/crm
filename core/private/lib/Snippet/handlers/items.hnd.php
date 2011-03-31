<?php

	class Snippet_hnd_items extends Snippets_Handlers_Commons{
	
		protected function handle_viewItem2(){
		
		}
		protected function handle_viewItem(){
		
			if( $_POST['xajax'] == 'addSnippet' ){
				oNav()->getPage("{$this->code}Info", (array)$this->params['filters']);
				return '';
			}
			
/* Array(
	[code] => customers
	[snippet] => viewItem
	[filters] => 129
	[writeTo] => 
	[modifier] => null
	[group_uID] => 0.64424800 1301574930
	[initialize] => 
) */
			$data = $this->Source->getData('item', $this->params['filters']);
			
			# Form data blocks (for presentational purposes)
			$block = 0;
			$blocks = array();

			foreach( $this->fields as $field => $atts ){
				($field == '>') ?  $block++ : $blocks[$block][$field] = $atts;
			}
			
			$this->assign('data', $data);
			$this->assign('blocks', $blocks);
			$this->assign('objectID', $this->params['filters']);
		
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