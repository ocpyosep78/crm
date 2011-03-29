<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/***************
** This class is a variables holder. All logic and methods lie on class PageCfg.
** It is meant to supply all dynamic info for the frame template (CORE_TEMPLATES.'main.tpl')
***************/
		
	class Page{
		
		# Main box
		public $template;
		public $content;
		
		# App constants
		public $appName;
		public $appTitle;
		public $appImg;
		
		# Navigation buttons (modules)
		public $modules;
		public $module;
		public $navButtons;
		
		# Menu (pages)
		public $page;
		public $menuItems;
		public $pageNav;
		
		# Styles
		public $styleSheets;
		
		# javaScript
		public $jScripts;
		public $jsCode;
		public $jsCodeOnLoad;
		
		# Debugger Box
		public $debugger;
		public $debugHeader;
		public $develMsgs;
		public $errMsgs;
		
		
		public function __construct(){
			$this->template		= EMPTY_TPL;
			$this->content		= '';
			$this->appName		= APP_NAME;
			$this->appTitle		= APP_NAME;
			$this->appImg		= APP_IMG;
			$this->modules		= array();
			$this->module		= '';
			$this->navButtons	= array();
			$this->page		= '';
			$this->menuItems	= array();
			$this->pageNav	= array();
			$this->styleSheets	= array();
			$this->jScripts		= array();
			$this->jsCode		= '';
			$this->jsOnLoad		= '';
			$this->debugger		= false;
			$this->debugHeader	= '';
			$this->develMsgs	= array();
			$this->errMsgs		= array();
		}
	
	}

?>