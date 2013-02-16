<?php

isset($_GET['chatCheck']) && die();

// Try the old code (if it returns, we'll go on with the new one)
if (!$_GET || !empty($_GET['nav']))
{
	require_once 'app/index.php';
	exit;
}

define('BASE', dirname(__FILE__));
define('PATH_PVT', BASE . '/private');
define('PATH_PUB', BASE . '/public');


// Load config files (defines)
require_once PATH_PUB . '/cfg/config.cfg.php';
require_once PATH_PVT . '/cfg/core.cfg.php';

// Local config file, if exists
$devCfg = PATH_PUB . '/cfg/local.cfg.php';
is_readable($devCfg) && require_once $devCfg;


// Set environment
session_start();
putenv("LANG=spanish");
setlocale(LC_ALL, 'spanish');
date_default_timezone_set(TIME_ZONE);


// Common functions
require_once PATH_PUB . '/libs/common.php';


// Basic classes and traits
require_once TRAITS . '/Connect.php';
require_once TRAITS . '/Singleton.php';

require_once PATH_PVT . '/Exceptions.php';
require_once PATH_PVT . '/Object.php';

require_once LIBS . '/Datasource/Datasource.php';

require_once LIBS . '/ModelView/Model.php';
require_once LIBS . '/ModelView/View.php';

require_once LIBS . '/Access/Access.php';
require_once LIBS . '/Snippet/Snippet.php';
require_once LIBS . '/Template/Template.php';

// Global objects Builder (creates each global object on demand)
require_once CORE_LIB . '/Builder/Builder.class.php';

// FileForm (for 'ajax' submit of forms with file inputs)
require_once CLASSES_PATH . '/FileForm/FileForm.php';

// Controllers
require_once PATH_PVT . '/Controllers/Controller.php';

// Response Handler
require_once LIBS . '/Response/Response.php';


// Sugar (user-friendly page names)
is_file('public/Sugar.php') && (require_once 'public/Sugar.php');

if (!class_exists('Sugar'))
{
	class Sugar
	{
		public function page(){
			return isset($pages[$page]) ? $pages[$page] : $page;
		}
	}
}

require_once MODS_PATH . '/pages.php';
require_once MODS_PATH . '/funcs.php';


/*******/
/* D E B U G G I N G
/*****/

error_reporting(devMode() ? E_ALL : E_ERROR);
devMode() && set_error_handler('error_handler');

// Call for showing debug stats frame (will exit without coming back)
isset($_GET['stats']) && devMode() && require_once 'debug/stats.php';


/***************
** P R O C E S S   A J A X   C A L L S
***************/

try
{
	Controller::process();
}
catch (PublicException $e)
{
	header('Content-type: text/xml');
	echo $e->getMessage();

	exit;
}
catch (Exception $e)
{
	header('Content-type: text/xml');
	$error = devMode() ? $e->getMessage() : 'Ocurrió un error inesperado';
	echo $error;

	exit;
}