<?php

# Version
define('VERSION', '1.0.0');
define('VERSION_STATUS', 'alpha', true);
define('LAST_UPDATE', '2012-11-24');


# Home | "model[:page = main]", e.g. 'home', 'home:main', 'users:me', etc.
define('HOME', 'agenda');

# Local
define('MAIN_LOCATION', 'Montevideo');
define('TIME_ZONE', 'America/Montevideo');


# Paths
define('CLASSES_PATH', PATH_PUB . '/libs/classes');
define('THIRD_PARTY_PATH', PATH_PUB . '/libs/third-party');
define('EXPORT_PDF_PATH', PATH_PUB . '/export/pdf');
define('IMAGES_PATH', PATH_PUB . '/images');
define('MODS_PATH', PATH_PUB . '/mods');
define('TEMPLATES_PATH', PATH_PUB . '/templates');

define('IMAGES_URL', URL_PUB . '/images');


# Application
define('APP_NAME', 'CRM / INGETEC', true);

define('UI_THEME', empty($_GET['theme']) ? 'redmond' : $_GET['theme']);

# Chat
define('CHAT_ADDRESS', 'http://www.ingetec.com.uy/chat', true);


# Database access
define('DS_HOST', 'localhost', true);
define('DS_USER', 'crm', true);
define('DS_PASS', 'crm', true);
define('DS_SCHEMA', 'crm', true);


# fPDF
define('PDF_PAGE', 'A4');
define('PDF_FONT', 'Arial');
define('PDF_FONT_SIZE', 12);
define('PDF_CELL_BORDER', 0, true);		/* 1 for designing/testing/debugging, 0 for production */