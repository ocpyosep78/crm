<?php

define('BASE', dirname(__FILE__));
define('CORE', BASE . '/core');


// Load config files (defines)
require_once BASE . '/app/cfg/config.cfg.php';
require_once CORE . '/cfg/core.cfg.php';

// Local config file, if exists
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

require_once CORE . '/Exceptions.php';
require_once CORE . '/Object.php';

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
require_once CORE . '/Controllers/Controller.php';

// Response Handler
require_once LIBS . '/Response/Response.php';


// Sugar (user-friendly page names)
is_file('app/Sugar.php') && (require_once 'app/Sugar.php');

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