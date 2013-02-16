<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	/**
	 * Config has only one real enter point, page config. There it shows several
	 * tabs (depending on user's permissions), and each links to a subpage. Each
	 * of these subpages has an associated permission named subCfg{$code}. Default
	 * config subpage's code is 'Main' (it has no permission associated, other than
	 * 'config', as it's the main page of the module).
	 *
	 * This function discriminates calls to each tab, and delivers responsability
	 * to functions subCfg{$code}, located in mods/config/funcs.php.
	 * 
	 * In general lines, responsible functions will define call methods of class
	 * Config [called as oMod()]. Back to page_config, the page will
	 * be presented, with chosen tab selected and content defined by subpage's code.
	 *
	 * Content is found in {TEMPLATES_PATH}/config/tab{$code}.
	 */
	function page_config( $code='Main' ){
	
		# Clear permissions, modules, pages, areas, from cache (to stay updated always)
		oPermits()->clear();
		
		# Get list of available tabs, and make sure it's available (or fall down to default)
		if( !oConfig()->tabExists( $code ) ) $code = 'Profiles';//Main';
		
		# Call responsible function to build subPage (content for this tab, that is)
		if( $code != 'Main' && oPermits()->can($tab="subCfg{$code}") ) $tab();
		else return oPermits()->noAccessMsg();
		
		oSmarty()->assign('tabs', oConfig()->getTabs());
		oSmarty()->assign('tab', $code);
		
		addScript("TAB = '{$code}';");
		
	}