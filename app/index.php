<?php

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

<<<<<<< HEAD
$urgentAjax = array(
	'sync'		=> 'sync',
	'getPage'	=> array('getPage', oNav(), 'getPage'),
	'showPage'	=> array('showPage', oNav(), 'showPage'),
);

foreach ($urgentAjax as $key => $code)
{
	oXajax()->registerFunction($code);

	if (isXajax($key))
	{

		try
		{
			oXajax()->processRequests();
		}
		catch (PublicException $e)
		{
			header('Content-type: text/xml');
			echo say(addslashes($e->getMessage()))->getXML();

			exit;
		}
		catch (Exception $e)
		{
			header('Content-type: text/xml');
			$error = devMode() ? $e->getMessage() : 'Ocurrió un error inesperado';
			echo say($error)->getXML();

			exit;
		}
=======
	$urgentAjax = array(
		'sync'		=> 'sync',
		'getPage'	=> array('getPage', oNav(), 'getPage'),
		'showPage'	=> array('showPage', oNav(), 'showPage'),
	);

	foreach( $urgentAjax as $key => $code ){
		oXajax()->registerFunction( $code );
		if( isXajax($key) ) oXajax()->processRequests();
>>>>>>> refactor
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

	oPageCfg()->set_appTitle( loggedIn() );
	oPageCfg()->add_styleSheets( getSkinCss() );
	oPageCfg()->add_jScripts(array(
		CORE_SCRIPTS.'mootools 1.3.js',
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
<<<<<<< HEAD
}
catch (PublicException $e)
{
	header('Content-type: text/xml');
	echo say($e->getMessage())->getXML();

	exit;
}
catch (Exception $e)
{
	header('Content-type: text/xml');
	$error = devMode() ? $e->getMessage() : 'Ocurrió un error inesperado';
	echo say($error)->getXML();

	exit;
}
=======
	oPajax()->processRequests();
>>>>>>> refactor



/***************
** D I S P L A Y   P A G E
***************/

	/* Skin */

	oSmarty()->assign('Xajax', oXajax());
	oSmarty()->assign('Pajax', oPajax());
	oSmarty()->assign('Page', oPageCfg()->getPage());

<<<<<<< HEAD
header("Content-Type: text/html; charset=utf8");
oSmarty()->display(getSkinTpl());
=======
	header("Content-Type: text/html; charset=iso-8859-1");
	oSmarty()->display( oNav()->inFrame ? FRAME_TPL_PATH : getSkinTpl() );
>>>>>>> refactor
