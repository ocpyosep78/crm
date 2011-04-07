<?php

	class Snippet_hnd_actions extends Snippets_Handlers_Commons{
	
		protected function handle_editField(){
		
			# $this->params['filters'] is an array with keys: id, field and value
			$filters = $this->params['filters'];
			$data = array($filters['field'] => $filters['value']);
			
			$ans = $this->Source->update($filters['id'], $data);
			
			if( $ans->error ) $this->Layers->get('ajax')->display($ans->msg);
			else{
				$_POST['xajax'] = 'getPage';
				oNav()->getPage("{$this->code}Info", (array)$this->params['filters']['id'],
					'El elemento fue modificado correctamente.', 1);
				return '';
			}
			
			/* BUG : when reloading the page (on success) commonList is not loaded
			   but other snippets are (bigTools, comboList) */

		}
	
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
		
			$ans = $this->Source->delete( $this->params['filters'] );
			
			if( $ans->error ) $this->Layers->get('ajax')->display($ans->msg);
			else{
				$_POST['xajax'] = 'getPage';
				oNav()->getPage($this->code, (array)$this->params['modifier'],
					'El elemento fue eliminado correctamente.', 1);
				return '';
			}
			
			/* BUG : when reloading the page (on success) commonList is not loaded
			   but other snippets are (bigTools, comboList) */
		
		}
		
	}

?>