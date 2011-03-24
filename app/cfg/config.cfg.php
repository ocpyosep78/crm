<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	/**
	 * BASE_PATH constant is defined before loading this script, and points
	 * to site's root.
	 */
	
	# Version
	define('VERSION', '0.4.0');
	define('VERSION_STATUS', '', true);
	define('LAST_UPDATE', '2010-08-08');

	# General
	define('DIR_SEP', DIRECTORY_SEPARATOR);
	define('TIME_ZONE', 'America/Montevideo');
	
	# Paths
	define('APP_IMG', 'app/core/app.png');
	define('CLASSES_PATH', 'app/libs/classes/');
	define('CORE_PATH', 'app/core/');
	define('EXPORT_PDF_PATH', 'app/export/pdf/');
	define('FUNCTIONS_PATH', 'app/libs/functions/');
	define('IMG_PATH', 'app/images/');
	define('MODS_PATH', 'app/mods/');
	define('SCRIPTS_PATH', 'app/scripts/');
	define('SKINS_PATH', 'app/skins/');
	define('STYLES_PATH', 'app/styles/');
	define('TEMPLATES_PATH', 'app/templates/');
	define('FRAME_TPL_PATH', '../core/frame.tpl');
	define('FRAME_CSS_PATH', 'app/core/frame.css');

	# Application
	define('APP_NAME', 'CRM / INGETEC', true);
	define('DEFAULT_PAGE', 'home');
	define('DEFAULT_PAGE_ATTS', serialize(array()));
	define('PERMITS_CACHE_TIMEOUT', 1800, true);		/* Time to cache Permissions and structure */
	define('PAGE_CONTENT_BOX', 'main_box');
//	define('SKIN', 'new', true);
	
	# Agenda
	define('AGENDA_DAYS_TO_SHOW', 7);
	
	# Chat
	define('CHAT_ADDRESS', 'http://www.ingetec.com.uy/chat', true);
	
	# Connection Class and subclasses
	define('CONNECTION_PATH', 'app/libs/classes/SQL/Connection/Connection.class.php');	# Whole path
	define('SQL_PATH', 'app/libs/classes/SQL/');										# Folder

	# Database access
	define('CRM_HOST', 'localhost', true);
	define('CRM_USER', 'root', true);
	define('CRM_PASS', 'it707', true);
	define('CRM_DB', 'crm_ingetec', true);
	
	# Debugging
	define( 'DEVELOPER_MODE', false, true);
	
	# fPDF
	define('PDF_PAGE', 'A4');
	define('PDF_FONT', 'Arial');
	define('PDF_FONT_SIZE', 12);
	define('PDF_CELL_BORDER', 0, true);		/* 1 for designing/testing/debugging, 0 for production */
	
	# Logs
	define('MAX_LOGS_GLOBAL', 5000);
	define('MAX_ALERTS_PER_USER', 50);
	
	# Xajax
	define('XAJAX_VERBOSE', false, true);

?>