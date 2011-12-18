<?php

/**
 * class Modules_SQL_Engines
 * 
 * @overview: serves as parent to SQL engine scripts. While the particular
 *            engine that inherits from this class handles specialized
 *            rules to build queries for a particular DBMS, this class
 *            provides general methods and logic that applies to any engine
 */

	class Modules_SQL_Engine{
		
		static public function load( $engine ){

			$class = 'Modules_Engine_'.strtolower($engine);
			$path = dirname(__FILE__).'/../engines/'.strtolower($engine).'.engine.php';

			if( is_file($path) ) require_once( $path );
			
			class_exists($class)
				or die("Modules Engine: failed loading required engine {$engine}");
			
			return new $class;
			
		}
		
	}