<?php

define('CORE_PRIVATE', CORE_PATH . '/private');

# Core paths
define('CORE_LIB', CORE_PRIVATE . '/lib');
define('CORE_THIRD_PARTY', CORE_PRIVATE . '/third-party');
define('CORE_SKINS', CORE_PATH . '/public/skins');
define('CORE_STATIC', CORE_PRIVATE . '/static');
define('CORE_IMAGES', CORE_STATIC . '/images');
define('CORE_STYLES', CORE_STATIC . '/styles');
define('CORE_SCRIPTS', CORE_STATIC . '/scripts');
define('CORE_TEMPLATES', CORE_PRIVATE . '/templates');


// Model & View basics
define('CORE_BASE', CORE_PATH . '/base');


# Core shortcut to library paths
define('CONNECTION_PATH', CORE_LIB . '/Connection/Connection.php');


# Agenda
define('AGENDA_DAYS_TO_SHOW', 7);


# Debugging
define('DEVMODE', false, true);


# General
define('MAIN_TPL_PATH', CORE_TEMPLATES . '/main.tpl');
define('FRAME_CSS_PATH', CORE_STYLES . '/frame.css');


# Logs
define('LOGS_PATH', 'logs');
define('MAX_LOGS_GLOBAL', 5000);
define('MAX_ALERTS_PER_USER', 50);


# Permissions
define('PERMITS_CACHE_TIMEOUT', 1800, true);		/* Time to cache Permissions and structure */


# Xajax
define('XAJAX_JS_DIR', CORE_THIRD_PARTY . '/Xajax/xajax_js');
define('XAJAX_VERBOSE', false, true);