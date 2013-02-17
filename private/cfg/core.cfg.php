<?php

// URLs
define('PROTOCOL', $_SERVER['SERVER_PROTOCOL']);
define('URL', strtolower(current(explode('/', PROTOCOL))) . "://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
define('BBURL', dirname(URL));

define('URL_PUB', BBURL . '/public');
define('URL_PVT', BBURL . '/private');

// Relative urls for to static content, for PHP use
define('PATH_STATIC', PATH_PVT . '/static');
define('PATH_SCRIPTS', PATH_STATIC . '/scripts');
define('PATH_IMAGES', PATH_STATIC . '/images');
define('PATH_STYLES', PATH_STATIC . '/styles');
define('PATH_TPLS', PATH_STATIC . '/templates');

// Absolute urls for static content, for template use
define('URL_STATIC', URL_PVT . '/static');
define('URL_SCRIPTS', URL_STATIC . '/scripts');
define('URL_IMAGES', URL_STATIC . '/images');
define('URL_STYLES', URL_STATIC . '/styles');
define('URL_SKINS', URL_PVT . '/skins');

define('PATH_LIBS', PATH_PVT . '/lib');
define('PATH_THIRDPARTY', PATH_LIBS . '/third-party');
define('PATH_TRAITS',      PATH_LIBS . '/Traits');

# Old core paths
define('PATH_OLDLIB',    PATH_PVT . '/oldlib');
define('CONNECTION_PATH', PATH_OLDLIB . '/Connection/Connection.php');

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