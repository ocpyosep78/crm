<?php

function db($var, $die=true)
{
	headers_sent() || header('Content-Type: application/json');
	$var ? print_r($var) : var_dump($var);
	echo "\n";
	$die && die();
}


/***************
** I N I T I A L   C O N F I G U R A T I O N
***************/

# Site constants definitions (cfg/config.cfg.php)
# PHP configuration (time limit, memory limit, session_start, env & locale, timezone)
# Developer's local config (cfg/local.cfg.php)
# PHP common and app-specific functions (libs/common.php and FUNCTIONS_PATH)
require_once('initialize.php');


									try
									{

										if (isset($_GET['t']) && $_GET['t'] == 'create')
										{
											header('Content-type: text/xml');
											echo SNP::snp('createItem', 'Customer', ['action' => 'dialog'])->getXML();

											die();
										}

										if (isset($_GET['t8']))
										{
											db(Model::get('Customer')->columns());

											die();
										}

										if (isset($_GET['t']) && $_GET['t'] == 'edit')
										{
											db(SNP::snp('editItem', 'Customer', array('id' => 1)));

											die();
										}

										if (isset($_GET['t']) && $_GET['t'] == 'view')
										{
											db(SNP::snp('viewItem', 'Customer', array('id' => 1)));

											die();
										}

										if (isset($_GET['t']) && $_GET['t'] == 'list')
										{
											db(SNP::snp('commonList', 'Customer'));

											die();
										}

										if (isset($_GET['t4']))
										{
											db(SNP::snp('viewItem', 'user', array('filters' => 'dbarreiro')));

											die();
										}

										if (isset($_GET['t3']))
										{
											db(SNP::snp('commonList', 'User'));

											die();
										}

										// TESTING
										if (isset($_GET['t2']))
										{
											$filters[] = 'user IS NOT NULL';
											$filters[] = array('user IS NOT NULL');
											$filters[] = "user IN ('dbarreiro', 'Dios')";
											$filters[] = "user LIKE '%d%'";

											foreach ($filters as $filter)
											{
												echo "Filter: ";
												db($filter, false);
												echo "\n";
												db(Model::get('user')->find($filter, 'user, name', 3)->get(), false);
												echo "\n\n";
											}

											die();
										}

										if (isset($_GET['t1']))
										{
											$t = Model::get('user');

											$filters = array('NOT blocked', 'user' => 'Dios');
											$fields = array('user', "CONCAT(name, ' ', lastName) AS fullName", 'phone', 'email', 'profile');

											echo "\n" . '$q = $t->find($filters, $fields, 2)'."\n";
											$q = $t->find($filters, $fields, '2');
											db($q->get(), false);

											echo "\n" . '$q->flat()'."\n";
											$q->flat();
											db($q->get(), false);

											echo "\n" . '$q->ns(2)'."\n";
											$q->ns(2);
											db($q->get(), false);

											echo "\n" . '$q->convert("col")'."\n";
											$q->convert('col');
											db($q->get(), false);

											echo "\n" . '$q->ns(1)'."\n";
											$q->ns(1);
											db($q->get(), false);

											echo "\n" . '$q->convert("array")'."\n";
											$q->convert('array');
											db($q->get(), false);

											echo "\n" . '$q->flat()'."\n";
											$q->flat();
											db($q->get(), false);

											echo "\n" . '$q->convert("res")'."\n";
											$q->convert('res');
											db($q->get(), false);

											die();
										}
									}
									catch (Exception $e)
									{
										db($e->getMessage());
									}


/***************
** U R G E N T   X A J A X   F U N C T I O N S
***************/

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
	}
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

oPageCfg()->set_debugger(DEVMODE || (getSes('id_profile') == 1));

# Call for showing debug stats frame
if (isset($_GET['stats']) && (DEVMODE || (getSes('id_profile') == 1)))
{
	require_once('debug/stats.php');
}

addScript( 'window.DEVMODE = '.((int)DEVMODE).';' );


/***************
** G L O B A L   X A J A X   F U N C T I O N S   ( R E G U L A R )
***************/

oXajax()->registerFunction('login');
oXajax()->registerFunction(array('loadContent', oNav(), 'loadContent'));

if (loggedIn())
{
	oXajax()->registerFunction('logout');
	oXajax()->registerFunction('takeCall');
	oXajax()->registerFunction(array('switchTab', oTabs(), 'switchTab'));
	oXajax()->registerFunction(array('snippet', 'SNP', 'snp'));
}



/***************
** P A G E C F G
***************/

if (!empty($_GET['theme']))
{
	oSmarty()->assign('jQueryUiTheme', $_GET['theme']);
}
else
{
//	oSmarty()->assign('jQueryUiTheme', 'dot-luv');
//	oSmarty()->assign('jQueryUiTheme', 'start');
//	oSmarty()->assign('jQueryUiTheme', 'dark-hive');
//	oSmarty()->assign('jQueryUiTheme', 'ui-lightness');
//	oSmarty()->assign('jQueryUiTheme', 'ui-darkness');
//	oSmarty()->assign('jQueryUiTheme', 'smoothness');
	oSmarty()->assign('jQueryUiTheme', 'redmond');
}

oSmarty()->assign('core_scripts', CORE_SCRIPTS);

oPageCfg()->set_appTitle( loggedIn() );
oPageCfg()->add_styleSheets(getSkinCss());

oPageCfg()->add_jsCode("window.loggedIn = '".loggedIn()."'");

if (oNav()->inFrame)
{
	oPageCfg()->add_styleSheets(FRAME_CSS_PATH);
}


/***************
** P A G E   M A N A G E R
***************/

# User regularly logged in
if (loggedIn())
{
	require_once(CORE_PRIVATE . 'pageMgr.php');

	if (isset($_GET['load']) && is_callable($_GET['load']))
	{
		$_GET['load']();
	}
}
elseif (!isXajax())
{
	oPageCfg()->set_appTitle('Iniciar sesion');
	oPageCfg()->set_content(CORE_TEMPLATES . 'login.tpl');
	oNav()->processQueuedMsg();
}



/***************
** P R O C E S S   A J A X   C A L L S
***************/

try
{
	oXajax()->processRequests();
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

FileForm::processRequests();


/***************
** D I S P L A Y   P A G E
***************/

/* Skin */

oSmarty()->assign('Xajax', oXajax());
oSmarty()->assign('Page', oPageCfg()->getPage());

header("Content-Type: text/html; charset=iso-8859-1");
oSmarty()->display(getSkinTpl());