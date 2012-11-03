<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/**********************************************************************************
** 
** The app has only one entry point: this file. All calls, ajax or direct, start
** and end in this script. If a user is logged, module's engine handles the logic
** related to pages and modules: CORE_PRIVATE.'pageMgr.php'.
** 
** What follows is a short summary of what index.php does.
** 
** 
** INITIALIZE.PHP
** 
** - It sets PHP config parameters like error_reporting, time limit, etc.
** - It sends headers
** - It loads main files like config.cfg.php (constants), local.cfg.php (devel
**   constants).
** - It autoloads general functions (folder app/libs/functions/)
** 
** 
** BUILDER CLASS
** 
** - It initializes Builder object, that in turn is capable of initializing and
**	 returning most global objects, on demand. These objects can then be called
**	 later (acting somehow like singletons) with $Builder->get(objectName) or
**	 through handy shortcuts (functions) o{objectName}():
** 
**		- oSQL()						Communication with mySQL database
**		- oSmarty()						Templates
**		- oXajax & oXajaxResp()			Ajax
**		- oPageCfg()					Page layout, menu and other basic elements
**		- oValidate()					Input validation
**		- oFT() (FormTable)				Simple Forms in basic Table format
** 
** 
** PAGEMGR.PHP
** 
** - The application is composed of atomic pages. In general, no page depends
** on other pages (though disencouraged, there might be a few exceptions). So,
** pages are also loaded in the most automatized way possible. See doc header in
** CORE_PRIVATE.'pageMgr.php' for more information on pages, modules and the way
** they are handled.
** 
** - If conditions are met, debugging mode is turned on, adding a debug messages
** box to the main template (CORE_TEMPLATES.'main.tpl') and setting error_reporting value
** to E_ALL. A user-defined error-handling function prepares the output to be sent
** to this box for developpers to see.
** 
** - Finally, this script's in charge again after returning from pageMgr, and it
** either displays the page (through Smarty) or answers the requests (through Xajax)
** if there is one.
**
** - Some particular Xajax calls are handled right after creating Builder, because
**	 they don't need pageMgr and it saves time not to call it needlessly. These calls
**	 still have access to main objects through Builder, anyway. Mostly, these are
**	 sync calls and getPage (first step to request a new page, see Nav class for more
**	 info on the navigation system). To add more, see section URGENT AJAX CALLS.
**
** 
***********************************************************************************
** 
** O T H E R   I M P O R T A N T   D O C U M E N T A T I O N   (OUT OF DATE INFO)
** 
** 
** For more information on basic objects, procedures, functions and concepts see:
** 
** - dev.info.php			Links to most important doc headers, briefly explained
** 
**********************************************************************************/


/***************
** I N I T I A L   C O N F I G U R A T I O N
***************/
	
	# Site constants definitions (cfg/config.cfg.php)
	# PHP configuration (time limit, memory limit, session_start, env & locale, timezone)
	# Developer's local config (cfg/local.cfg.php)
	# PHP common and app-specific functions (libs/common.php and FUNCTIONS_PATH)
	require_once( 'initialize.php' );



/***************
** U R G E N T   X A J A X   F U N C T I O N S
***************/

	$urgentAjax = array(
		'sync'		=> 'sync',
		'getPage'	=> array('getPage', oNav(), 'getPage'),
		'showPage'	=> array('showPage', oNav(), 'showPage'),
	);
	
	foreach( $urgentAjax as $key => $code ){
		oXajax()->registerFunction( $code );
		if( isXajax($key) ) oXajax()->processRequests();
	}
	
	

/***************
** S M A R T Y   V A R S   (some general-use variables for Smarty's workspace)
***************/

	loadMainSmartyVars();



/***************
** D E B U G G I N G
***************/

	# Start Debugging if conditions are met (developer mode)
	define('APP_PATH', win2unix(dirname($_SERVER['SCRIPT_FILENAME'])).'/');
	oPageCfg()->set_debugger( DEVELOPER_MODE || getSes('id_profile') == 1 );
	# Call for showing debug stats frame
	if(isset($_GET['stats']) && (DEVELOPER_MODE || getSes('id_profile') == 1)){
		require_once('debug/stats.php');
	}
	addScript( 'window.DEVELOPER_MODE = '.((int)DEVELOPER_MODE).';' );
	

/***************
** G L O B A L   X A J A X   F U N C T I O N S   ( R E G U L A R )
***************/
	
	oXajax()->registerFunction('login');
	oXajax()->registerFunction(array('loadContent', oNav(), 'loadContent'));
	if( loggedIn() ){
		oXajax()->registerFunction('logout');
		oXajax()->registerFunction('takeCall');
		oXajax()->registerFunction(array('updateList', oLists(), 'updateList'));
		oXajax()->registerFunction(array('switchTab', oTabs(), 'switchTab'));
		oXajax()->registerFunction(array('addSnippet', oSnippet(), 'addSnippet'));
	}



/***************
** P A G E C F G
***************/
	
	$jQueryUiTheme = 'dot-luv';

	oPageCfg()->set_appTitle( loggedIn() );
	oPageCfg()->add_styleSheets(array(
		getSkinCss(),
		"//ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/{$jQueryUiTheme}/jquery-ui.css"));

	oPageCfg()->add_jScripts(array(
		CORE_SCRIPTS.'mootools 1.3.js',
		'//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js',
		'//ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/jquery-ui.min.js',
		CORE_SCRIPTS.'libs.js',
		CORE_SCRIPTS.'common.js'));
	oPageCfg()->add_jsCode("window.loggedIn = '".loggedIn()."'");
	
	if( oNav()->inFrame ) oPageCfg()->add_styleSheets( FRAME_CSS_PATH );
	


/***************
** M O D U L E S			/* TEMP */ /*
***************/

	oPageCfg()->add_jScripts(SNIPPET_PATH.'/output/scripts/Snippet.js');
	oPageCfg()->add_styleSheets(SNIPPET_PATH.'/output/styles/Snippet.css');



/***************
** P A G E   M A N A G E R
***************/

	# User regularly logged in
	if( loggedIn() ) require_once(CORE_PRIVATE.'pageMgr.php');
	# No user logged in
	elseif( !isXajax() ){
		oPageCfg()->set_appTitle('Iniciar sesion');
		oPageCfg()->set_content(CORE_TEMPLATES.'login.tpl');
		oNav()->processQueuedMsg();
	}
	
	

/***************
** P R O C E S S   A J A X   C A L L S
***************/
	
	oXajax()->processRequests();
	oPajax()->processRequests();



/***************
** D I S P L A Y   P A G E
***************/

	/* Skin */

	oSmarty()->assign('Xajax', oXajax());
	oSmarty()->assign('Pajax', oPajax());
	oSmarty()->assign('Page', oPageCfg()->getPage());
	
	header("Content-Type: text/html; charset=iso-8859-1");
	oSmarty()->display( oNav()->inFrame ? FRAME_TPL_PATH : getSkinTpl() );