<?php

# Version
define('VERSION', '1.0.0');
define('VERSION_STATUS', 'alpha', true);
define('LAST_UPDATE', '2012-11-24');


# Local
define('MAIN_LOCATION', 'Montevideo');
define('TIME_ZONE', 'America/Montevideo');


# Paths
define('CLASSES_PATH', 'app/libs/classes');
define('THIRD_PARTY_PATH', 'app/libs/third-party');
define('EXPORT_PDF_PATH', 'app/export/pdf');
define('IMG_PATH', 'app/images');
define('MODS_PATH', 'app/mods');
define('SCRIPTS_PATH', 'app/scripts');
define('TEMPLATES_PATH', 'app/templates');


# Application
define('APP_NAME', 'CRM / INGETEC', true);
define('DEFAULT_PAGE', 'home');
define('DEFAULT_PAGE_ATTS', serialize(array()));
define('SKIN', NULL, true);


# Chat
define('CHAT_ADDRESS', 'http://www.ingetec.com.uy/chat', true);


# Database access
define('DS_HOST', 'localhost', true);
define('DS_USER', 'root', true);
define('DS_PASS', 'it707', true);
define('DS_SCHEMA', 'crm_ingetec', true);


# fPDF
define('PDF_PAGE', 'A4');
define('PDF_FONT', 'Arial');
define('PDF_FONT_SIZE', 12);
define('PDF_CELL_BORDER', 0, true);		/* 1 for designing/testing/debugging, 0 for production */