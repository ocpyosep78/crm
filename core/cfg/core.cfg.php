<?php

// URLs
define('PROTOCOL', $_SERVER['SERVER_PROTOCOL']);
define('URL', strtolower(current(explode('/', PROTOCOL))) . "://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
define('BBURL', dirname(URL));

// Relative urls for static content, relative paths for PHP use
define('SCRIPTS', 'core/static/scripts');
define('IMAGES',  'core/static/images');
define('STYLES',  'core/static/styles');

define('TEMPLATES', CORE . '/static/templates');

define('LIBS', CORE . '/lib');
define('THIRD_PARTY', LIBS . '/third-party');
define('TRAITS',      LIBS . '/Traits');

# Old core paths
define('CORE_LIB',    CORE . '/oldlib');
define('CORE_SKINS',  CORE . '/skins');


# Core shortcut to library paths
define('CONNECTION_PATH', CORE_LIB . '/Connection/Connection.php');


# Agenda
define('AGENDA_DAYS_TO_SHOW', 7);


# Debugging
define('DEVMODE', false, true);


# Logs
define('LOGS_PATH', 'logs');
define('MAX_LOGS_GLOBAL', 5000);
define('MAX_ALERTS_PER_USER', 50);


# Permissions
define('PERMITS_CACHE_TIMEOUT', 1800, true);		/* Time to cache Permissions and structure */


# Xajax
define('XAJAX_JS_DIR', 'core/lib/third-party/Xajax/xajax_js');
define('XAJAX_VERBOSE', false, true);