<?php


// This script is meant to be included in another script, not called directly
(count(get_included_files()) !== 1) or die();


class PublicException extends Exception {}


define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
chdir (BASE_PATH);


define('CORE_PATH', 'core');



// Site definitions, and framework definitions, resp.
require_once BASE_PATH . '/app/cfg/config.cfg.php';
require_once CORE_PATH . '/private/cfg/core.cfg.php';

// Local config (for developpers only)
if (is_readable(BASE_PATH . '/app/cfg/local.cfg.php'))
{
	require_once BASE_PATH . '/app/cfg/local.cfg.php';
}


session_start();
putenv("LANG=spanish");
setlocale(LC_ALL, 'spanish');
date_default_timezone_set(TIME_ZONE);


// PHP Common Functions (reusable code, app-independant)
require_once BASE_PATH . '/app/libs/common.php';


// Basic classes and traits
require_once CORE_BASE . '/Object/Object.php';

require_once CORE_BASE . '/Datasource/Datasource.php';

require_once CORE_BASE . '/Model.php';
require_once CORE_BASE . '/View.php';

require_once CORE_BASE . '/Snippets/Snippet.php';


# Global objects Builder (creates each global object on demand)
require_once CORE_LIB . '/Builder/Builder.class.php';

# FileForm (for 'ajax' submit of forms with file inputs)
require_once CLASSES_PATH . '/FileForm/FileForm.php';