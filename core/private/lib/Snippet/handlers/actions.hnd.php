<?php

	class Snippet_hnd_actions extends Snippets_Handler_Commons{
	
		protected function handle_editField(){
		
			# $this->params['filters'] is an array with keys: id, field and value
			$filters = $this->params['filters'];
			$data = array($filters['field'] => $filters['value']);
			
			# Validate input or abort edition
			if( !$this->validateInput($data) ) return '';
			
			# If validation succeeded, continue to save new data
			$ans = $this->Source->update($filters['id'], $data);
			if( $ans->error ){
				$this->Layers->get('ajax')->display($ans->msg);
			}
			else{			/* TEMP */
				$_POST['xajax'] = 'getPage';
				oNav()->getPage("{$this->code}Info", (array)$this->params['filters']['id'],
					'El elemento fue modificado correctamente.', 1);
			}
			
			return '';
			
			/* BUG : when reloading the page (on success) commonList is not loaded
			   but other snippets are (bigTools, comboList) */

		}
	
		protected function handle_create( $keys=NULL ){
		
			$data = $this->params['filters'];
			
			# If there's a method defined to prefetch input, call it
			$this->Source->prefetchInput( $data );
			
			# Validate input if a ruleset was given, abort if it fails
			if( !$this->validateInput($data) ) return '';
			
			# Attempt to insert / edit item
			if( $keys ){		# Edit
				$ans = $this->Source->update($keys, $data);
			}
			else{				# Create
				$ans = $this->Source->insert( $data );
			}
			
			# On success move to viewItem page, on failure return an error message
			if( $ans->error ){
				$this->Layers->get('ajax')->display( $ans->msg );
			}
			else{			/* TEMP */
				if( !$keys ) $keys = $ans->ID;
				$_POST['xajax'] = 'getPage';
				oNav()->getPage("{$this->code}Info", (array)($keys ? $keys : $ans->ID),
					'El elemento fue creado correctamente.', 1);
				$this->Source->onSuccess($data, $keys);
			}
			
			return '';
			
		}
	
		protected function handle_edit(){
			
			$keys = $this->params['filters']['__objectID__'];
			unset( $this->params['filters']['__objectID__'] );
		
			return $this->handle_create( $keys );
		
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