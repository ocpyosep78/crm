<?php

	class Snippet_hnd_items extends Snippets_Handler_Commons{
	
		protected function handle_viewItem(){
		
			/* TEMP : untill this library handles navigation issues */
			if( $_POST['xajax'] == 'addSnippet' && !$this->params['writeTo'] ){
				$_POST['xajax'] = 'getPage';
				oNav()->getPage("{$this->code}Info", (array)$this->params['filters']);
				return '';
			}
			
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
			$this->assign('editable', $this->Access->can('edit', $this->code));
			
			return $this->fetch( 'items/view' );
		
		}
	
		protected function handle_createItem($objectID=NULL, $data=NULL){
		
			/* TEMP : untill this library handles navigation issues */
			if( $_POST['xajax'] == 'addSnippet' && !$this->params['writeTo'] ){
				$_POST['xajax'] = 'getPage';
				oNav()->getPage('create'.ucfirst($this->code), (array)$this->params['filters']);
				return '';
			}
			
			$fieldsByType = $this->Source->getFieldsByType();
			foreach( $fieldsByType['list'] as $code => &$list ){
				$list['data'] = $this->Source->getListFor($code);
				isset($list['emptyField']) || $list['emptyField'] = true;
			}
			
			$this->assign('lists', $fieldsByType['list']);
			
			# Empty unless editting
			$this->assign('objectID', $objectID);
			$this->assign('data', $data);
		
			return $this->fetch( 'items/edit' );

		}
	
		protected function handle_editItem(){
		
			/* TEMP : untill this library handles navigation issues */
			if( $_POST['xajax'] == 'addSnippet' && !$this->params['writeTo'] ){
				$_POST['xajax'] = 'getPage';
				oNav()->getPage('edit'.ucfirst($this->code), (array)$this->params['filters']);
				return '';
			}
			
			$data = $this->Source->getData('item', $this->params['filters']);
	
			return $this->handle_createItem($this->params['filters'], $data);
		
		}
	
	}