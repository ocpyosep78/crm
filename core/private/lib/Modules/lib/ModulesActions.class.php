<?php

	/**
	 * After Modules sets a page, that page might attempt actions, usually
	 * within the library. This class handles those attempts, and performs
	 * the actions as requested when possible, or returns the right error
	 * message.
	 * 
	 * Actions other than printing pages or single elements (those are
	 * handled by other classes in the library) are always carried through
	 * ajax, so this class handles only ajax requests and their answers.
	 * 
	 * Extending ModulesBase, this class has access to all information from
	 * the definition file of the modules we're handling. Refer to ~Base
	 * documentation for more info on its methods and properties.
	 */
	class ModulesActions extends ModulesBase{
		
		public function ajaxDo(){
		
			# To avoid namespace crashes, action methods in this class are prefixed
			$this->type = "axn_{$this->type}";
			
			# Initialize, get keys and pass all to the template engine
			$this->initialize();
			$this->setCommonProperties();
			$this->feedTemplate();
			
			# Let the right method handle the rest, depending on $this->type
			$HTML = $this->{$this->type}();
		
/*
	'open'		=> 'abrir',
	'create'	=> 'agregar',
	'edit'		=> 'editar',
	'block'		=> 'bloquear',
	'unblock'	=> 'desbloquear',
	'delete'	=> 'borrar',
*/
		
		}
		
		protected function axn_open(){
			return 'open';
		}
		
		protected function axn_create(){
			return 'create';
		}
		
		protected function axn_edit(){
			return 'edit';
		}
		
		protected function axn_block(){
			return 'block';
		}
		
		protected function axn_unblock(){
			return 'unblock';
		}
		
		protected function axn_delete(){
		
			$table = $this->DP->getTables();
			$keys = $this->keysString2Array();
			
			$ans = $this->delete($table, $keys);
			
			if( !$ans->error ){
				$this->AjaxEngine->addScript( 'location.href = location.href;' );
			}
			else{
				test( $ans );
			}
			
		}
		
	}
	
?>