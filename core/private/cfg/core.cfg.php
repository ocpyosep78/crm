<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */
 
 	define('CORE_PRIVATE', 'core/private/');
 	define('CORE_PUBLIC', 'core/public/');
 
	# Core paths
	define('CORE_LIB', CORE_PRIVATE.'lib/');
	define('CORE_THIRD_PARTY', CORE_PRIVATE.'third-party/');
	define('CORE_SCRIPTS', CORE_PRIVATE.'scripts/');
	define('CORE_SKINS', CORE_PUBLIC.'skins/');
	define('CORE_STATIC', CORE_PRIVATE.'static/');
	define('CORE_IMAGES', CORE_STATIC.'images/');
	define('CORE_STYLES', CORE_STATIC.'styles/');
	define('CORE_MEDIA', CORE_STATIC.'media/');
	define('CORE_TEMPLATES', CORE_PRIVATE.'templates/');
	
	# Core shortcut to library paths
	define('CONNECTION_PATH', CORE_LIB.'Connection/Connection.class.php');
	define('MODULES_PATH', CORE_LIB.'Modules/');
	
	# Agenda
	define('AGENDA_DAYS_TO_SHOW', 7);
	
	# Debugging
	define('DEVELOPER_MODE', false, true);
	
	# General
	define('APP_IMG', CORE_IMAGES.'app.png');
	define('MAIN_TPL_PATH', realpath(CORE_TEMPLATES.'main.tpl'));
	define('FRAME_TPL_PATH', realpath(CORE_TEMPLATES.'frame.tpl'));
	define('FRAME_CSS_PATH', CORE_STYLES.'frame.css');
	
	# Logs
	define('MAX_LOGS_GLOBAL', 5000);
	define('MAX_ALERTS_PER_USER', 50);
	
	# Modules
	define('MOD_DEFINITIONS_PATH', CORE_PUBLIC.'/def/');
	
	# Permissions
	define('PERMITS_CACHE_TIMEOUT', 1800, true);		/* Time to cache Permissions and structure */
	
	# Xajax
	define('XAJAX_VERBOSE', false, true);

?>