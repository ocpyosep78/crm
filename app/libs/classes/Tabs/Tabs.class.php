<?php

	class Tabs{
	
		private $page;
		private $module;
		
		private $template=NULL;		/* For prefetched templates */
		private $HTML=NULL;			/* For prefetched templates */
	
		private $tabs=NULL;
		
		
		
		public function __construct(){
		
			$this->page = oNav()->getCurrentPage();
			$this->module = oNav()->getCurrentModule();
			
/* TEMP */ if( $this->page == 'home' ){
	$this->module = 'users';
	$this->page = 'usersInfo';
}
			
		}
		
		public function setPage( $page ){
		
			$this->page = $page;
			$this->module = oPermits()->getModuleFromPage( $page );
			
		}
		
		public function getTabs(){
		
			# We need to search for tabs only on first call
			if( !is_null($this->tabs) ) return $this->tabs;
		
			# Include tabs script for this module
			$tabs = $this->includeTabsScript();
			if( empty($tabs) ) return $this->tabs = array();
			
			foreach( $tabs as $tab => &$atts ){
				# Accept attributes with just a string (screenname for the tab)
				if( !is_array($atts) ) $atts = array('name' => $atts);
				# Check the function to draw this tab exists
				if( !function_exists($func="tab_{$this->page}_{$tab}") ){
					unset( $tabs[$tab] );
					continue;
				}
				# See if we need to take permissions into account
				if( isset($atts['permit']) && oPermits()->cant($atts['permit']) ){
					unset( $tabs[$tab] );
				}
			}
			
			return $this->tabs = isset($tabs) ? $tabs : array();
		
		}
		
		/**
		 * Passing a template path to this function makes the object stop looking for the
		 * right template (given by the module, page and tab codes). It simply fills the
		 * tabsContent box with received template.
		 */
		public function useThisTemplate( $template ){
		
			$this->template = $template;
			
		}
		
		/**
		 * Passing an HTML string to this function makes the object stop looking for the
		 * right template (given by the module, page and tab codes). It simply fills the
		 * tabsContent box with received HTML string.
		 */
		public function useThisHTML( $HTML='' ){
			
			$this->HTML = $HTML;
			
		}
		
		public function start( $HTML=NULL ){
		
			return $this->getHTML(NULL, $HTML);
			
		}
		
		public function getHTML($tab=NULL, $baseHTML=NULL){
		
			if( is_null($tab) ) $tab = array_shift(array_keys($this->getTabs()));
			
			oSmarty()->assign('baseHTML', $baseHTML);
			# Locate the template for the fixed part of the page (off-tabs)...
			$baseTpl = realpath(TEMPLATES_PATH."{$this->module}/{$this->page}.tpl");
			# ...tell Smarty about it...
			oSmarty()->assign('baseTpl', $baseTpl);
			# ...and display the tabs skeleton below it
			oNav()->updateContent( dirname(__FILE__).'/templates/tabs.tpl' );
			
			# Now we're ready to load the first tab through regular method switchTab
			return $this->switchTab( $tab );
			
		}
		
		public function switchTab( $tab ){
		
			if( !$this->getTabs() ) return oXajaxResp();
		
			# Get all passed params, and forward all but the tabCode (first one)
			$allowed = $tab && array_search($tab, array_keys($this->getTabs())) !== false;
			
			# Assign it in Smarty so the right template can be included
			oSmarty()->assign('tab', $tab);
			oSmarty()->assign('tabs', $this->getTabs());
			
			# Run the tab's function (so it can assign smarty vars)
			if( !$allowed ) return showStatus('No se puede cargar la pestaña solicitada.');
				
			# Call tab handler
			$postCalls = call_user_func_array("tab_{$this->page}_{$tab}", oNav()->getCurrentAtts());
			
			# Get content if template exists and no HTML string was set by the tab generating function
			$content = !is_null($this->HTML)
				? $this->HTML
				: $this->getTpl( $this->template ? $this->template : $tab );
			
			# Update tab area content
			addAssign('tabContent', 'innerHTML', $content);
			addAssign('tabButtons', 'innerHTML', $this->template('tabButtons'));
			
			# Make sure to have tabs running JS-wise
			addScript('initializeTabButtons();');
			
			# Javascript to be run after building content (one string or an array of strings)
			if( $postCalls ){
				if( is_string($postCalls) ) $postCalls = array( $postCalls );
				foreach( $postCalls as $call ) if( is_string($call) ) addScript( $call );
			}
			
			return oXajaxResp();
			
		}
		
		private function includeTabsScript(){

			# Include tabs script for this module
			if( !is_file($path=MODS_PATH.$this->module.'/tabs.php') ) return false;
			else $tabs = require_once( $path );
			
			return $tabs;
			
		}
		
		private function getTpl( $tab ){
		
			return ($path = realpath(TEMPLATES_PATH."{$this->module}/{$this->page}_{$tab}.tpl"))
				? oSmarty()->fetch( $path )
				: '';
			
		}
		
		private function template( $which ){
			
			return is_file($path=dirname(__FILE__)."/templates/{$which}.tpl")
				? oSmarty()->fetch( $path )
				: '';
			
		}
	
	}

?>