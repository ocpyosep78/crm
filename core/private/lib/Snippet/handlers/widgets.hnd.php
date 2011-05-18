<?php

	class Snippet_hnd_widgets extends Snippets_Handler_Commons{
	
		protected function handle_bigTools(){
		
			# Disable certain buttons depending on which snippet is using this one
			switch( $this->params['main'] ){
				case 'commonList':
				case 'simpleList':
					$this->disableBtns( 'list' );
					break;
				case 'createItem':
					$this->disableBtns( 'create' );
					break;
				case 'viewItem':
					$this->disableBtns( 'view' );
					break;
				case 'editItem':
					$this->disableBtns( 'edit' );
					break;
				case 'deleteItem':
					$this->disableBtns( 'delete' );
					break;
				default:
					break;
			}
		
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
				'selected'	=> $this->params['filters'],
			));
			
			return $this->fetch( 'widgets/comboList' );
			
		}
		
		protected function handle_intro(){
		
			return '';
		
		}
	
	}

?>