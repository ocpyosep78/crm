<?php

isset($_GET['chatCheck']) && die();


define('BASE', dirname(__FILE__));
define('CORE', BASE . '/core');


// Load config files (defines)
require_once BASE . '/app/cfg/config.cfg.php';
require_once CORE . '/cfg/core.cfg.php';

$devCfg = BASE . '/app/cfg/local.cfg.php';
is_readable($devCfg) && require_once $devCfg;


// Set environment
session_start();
putenv("LANG=spanish");
setlocale(LC_ALL, 'spanish');
date_default_timezone_set(TIME_ZONE);


// Common functions
require_once BASE . '/app/libs/common.php';


// Basic classes and traits
require_once TRAITS . '/Connect.php';
require_once TRAITS . '/Singleton.php';

require_once LIBS . '/Exceptions.php';
require_once LIBS . '/Object/Object.php';
require_once LIBS . '/Datasource/Datasource.php';

require_once LIBS . '/ModelView/Model.php';
require_once LIBS . '/ModelView/View.php';

require_once LIBS . '/Access/Access.php';
require_once LIBS . '/Snippet/Snippet.php';
require_once LIBS . '/Page/Page.php';

// Global objects Builder (creates each global object on demand)
require_once CORE_LIB . '/Builder/Builder.class.php';

// FileForm (for 'ajax' submit of forms with file inputs)
require_once CLASSES_PATH . '/FileForm/FileForm.php';


/*******/
/* D E B U G G I N G
/*****/

error_reporting(devMode() ? E_ALL : E_ERROR);
devMode() && oXajax()->statusMessagesOn();
devMode() && set_error_handler('error_handler');

// Call for showing debug stats frame
isset($_GET['stats']) && devMode() && require_once 'debug/stats.php';


/*******/
/* Xajax
/*****/


oXajax()->registerFunction('login');
oXajax()->registerFunction('sync');
oXajax()->registerFunction(['getPage', oNav(), 'getPage']);
oXajax()->registerFunction(['showPage', oNav(), 'showPage']);
oXajax()->registerFunction(['loadContent', oNav(), 'loadContent']);

if (loggedIn())
{
	oXajax()->registerFunction('logout');
	oXajax()->registerFunction('takeCall');
	oXajax()->registerFunction(['switchTab', oTabs(), 'switchTab']);
	oXajax()->registerFunction(['snippet', 'Snippet', 'snp']);
}

//db(Access::currentState());
foreach (glob(MODS_PATH . '/*/ajax.php') as $ajax)
{
	foreach((require_once $ajax) as $func)
	{
		oXajax()->registerFunction($func);
	}

}

if (!isXajax())
{
	echo Page::one()->page();
	die();
}

# User regularly logged in
if (loggedIn())
{
	require_once MODS_PATH . '/pages.php';
	require_once MODS_PATH . '/funcs.php';

	if (isset($_GET['load']) && is_callable($_GET['load']))
	{
		$_GET['load']();
	}
}
elseif (!isXajax())
{
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



					try
					{
						if (isset($_GET['t']) && $_GET['t'] == 'item')
						{
							header('Content-type: text/xml');
							echo Snippet::snp('viewItem', 'Customer', ['id' => 1])->getXML();

							die();
						}

						if (isset($_GET['t']) && $_GET['t'] == 'create')
						{
							header('Content-type: text/xml');
							echo Snippet::snp('createItem', 'Customer', 'dialog')->getXML();

							die();
						}

						if (isset($_GET['t8']))
						{
							db(Model::get('Customer')->columns());

							die();
						}

						if (isset($_GET['t']) && $_GET['t'] == 'edit')
						{
							db(Snippet::snp('editItem', 'Customer', array('id' => 1)));

							die();
						}

						if (isset($_GET['t']) && $_GET['t'] == 'view')
						{
							db(Snippet::snp('viewItem', 'Customer', array('id' => 1)));

							die();
						}

						if (isset($_GET['t']) && $_GET['t'] == 'list')
						{
							db(Snippet::snp('commonList', 'Customer'));

							die();
						}

						if (isset($_GET['t4']))
						{
							db(Snippet::snp('viewItem', 'user', array('filters' => 'dbarreiro')));

							die();
						}

						if (isset($_GET['t3']))
						{
							db(Snippet::snp('commonList', 'User'));

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