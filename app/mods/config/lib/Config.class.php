<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	require_once( dirname(__FILE__).'/SQL.Config.class.php' );
	


	class MOD_Config extends SQL_Config{
		
		/**
		 * List of tabs for config module (that is, for config page).
		 * 
		 * They all require, to be functional:
		 *	~ a permission (in DB) named subCfg{$code} (not needed for developers)
		 *	~ a function (in mods/config/funcs.php) named subCfg{$code}
		 *	~ a template (in templates/config/) named {$code}.tpl (will be the content)
		 * 
		 * If a permission is not set, no user (but devels) will see it.
		 * If the function does not exist, nobody can access it (quite obvious isn't it).
		 * If the template does not exist, a Smarty error will be raised.
		 */
		public function getTabs( $which=NULL ){
		
//			if( !function_exists("subCfgMain") ) trigger_error('Main config function was not found!');
		
//			$list['Main'] = 'Preferencias';									# Intro, Config preferences (always available)
			$tabs = array(
				# Admin (regular users)
				'Profiles'			=> 'Perfiles',
				'Alerts'			=> 'Alertas',							# Configure everything related to alerts
				# Devel (developers only, grouped DB tasks, with integrity checks, etc.)
				'devPermits'		=> 'Permisos',							# Permissions (add, edit, delete, hide, alias)
				'devMods'			=> 'Módulos',							# Modules list (add, edit, delete, tests)
				'devPages'			=> 'Páginas',							# Pages and Areas list (add, edit, delete, tests)
				'devInsert'			=> 'Editar Código',						# Allows developers to edit code via web
				'devQueries'		=> 'Consultas SQL',						# Allows developers to execute sql queries
			);
			foreach( $tabs as $code => $tab ){
				if(oPermits()->can("subCfg{$code}") && function_exists("subCfg{$code}")){
					$list[$code] = $tab;
				}
			}
		
			return $which ? ($which && isset($list[$which]) ? $list[$which] : NULL) : array_reverse($list);
			
		}
		
		public function tabExists( $code ){
		
			return $this->getTabs( $code );
			
		}
		
		
		
	}

?>