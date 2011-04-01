<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

/**
 * @name: Class Modules
 * @description: automates the creation and exexution of queries on modules,
 *               given a definition class for that module
 * @author: Diego Barreiro <diego.bindart@gmail.com>
 * @created: mar 2011
 * 
 * @overview: 
 * 
 * @in-depth: 
 * 
 * @structure: 
 */

	defined('MODULES_DEFINITIONS')
		|| define('MODULES_DEFINITIONS', dirname(__FILE__).'/defs');
		
	defined('MODULES_SQL_ENGINE')
		|| define('MODULES_SQL_ENGINE', 'mysql');
	
	require_once( dirname(__FILE__).'/lib/Engine.lib.php' );
	require_once( dirname(__FILE__).'/lib/Parser.lib.php' );
	

	class Modules{
		
		private $Engine;	# SQL database
		
		private $mod;		# Module's identifier
		private $sub;		# Module's category (variations of a module)
		
		public function __construct($mod, $cat=NULL, $engine=MODULES_SQL_ENGINE){
			
			$this->mod = $mod;
			$this->setCategory( $cat );
			
			$this->Engine = Modules_SQL_Engine::load( $engine );
		
		}
		
		public function setCategory( $cat ){
			
			$this->cat = $cat;
			$this->Parser = new Modules_Parser($this->mod, $cat);
			
		}
		
		public function setEngine( $engine ){
			
			$this->Engine = Modules_SQL_Engines::load( $engine );
			
		}
		
		/**
		 * Pre-parse definition file, storing results in an object
		 * of class Modules_Definitions. This will be the source
		 * for all further processing and automatic formatting of
		 * sql queries.
		 */
		public function readDefinition(){
			
			$data = $this->Parser->getBuffer();
			
			test( $data );
			
		}
	
	}

?>