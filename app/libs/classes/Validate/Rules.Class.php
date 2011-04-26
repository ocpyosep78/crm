<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	/* This class has no real logic. It is a frame to hold big sets of data for class Validate. */

	class Rules{
		
		/* Rules sets, read from folder ./ruleSets/ */
		public $ruleSets;

	
/***************
** E X P R E S S I O N S
***************/

		public $expressions = array(
			/* open: anything */
			'open'			=> '/^.*$/',
			/* text: most common symbols for regular latin1 texts, plus puntuation and quotes (double and single) */
			'text'			=> '/^[\w\-\.\,\;\(\)\/áéíóúàèìòùäëïöüñÁÉÍÓÚÑÀÈÌÒÙÄËÏÖÜ\"\'\s:]*$/',
			/* alpha: letters, numbers and underscore */
			'alpha'			=> '/^[a-zA-Z0-9_]*$/',
			/* alphaMixed: underscores, at least one letter, at least one number, and any extra amount of them */
			'alphaMixed'	=> '/^_*[a-zA-Z]+_*[0-9]+[a-zA-Z0-9_]*$|^_*[0-9]+_*[a-zA-Z]+[a-zA-Z0-9_]*$/',
			/* valid email addresses */
			'email'			=> '/^$|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/',
			/* num: numbers (length calculated as string) */
			'num'			=> '/^\d*$/',
			/* ranged: numbers (length calculated as a numeric range) */
			'ranged'		=> '/^\d*$/',
			/* bool: a boolean represented as an integer (either 0 or 1) */
			'bool'			=> '/^[01]$/',
			/* docNum: document numbers (100000-9999999, and optionally an extra check digit (i.e. 3073853-2) */
			'docNum'		=> '/^[\d\.-]*/',
			/* cost: signed 0.8 integer part digit with 0-2 decimal digits */
			'cost'			=> '/^-?\d{0,8}(\.\d{1,2})?$/',
			/* phone: numbers, dashes, spaces and parenthesis, any amount and order */
			'phone'			=> '/^[\d- \(\) \/\.]*$/',
			/* rut: 12 digits number */
			'rut'			=> '/^\d{12}$|^$/',
			/* time: well-formatted time stamp (i.e. 08:55) */
			'time'			=> '/^$|^(2[0-3]|[01]\d):[0-5]\d$/',
			/* date: well-formatted date stamp (i.e. 2009-12-05, 2008/02/28), 08-11-21 */
			'date'			=> '/^$|^(\d{4}|\d{2})[-\/]\d{2}[-\/]\d{2}$/',
			/* datetime: well-formatted timestamp (i.e. 2009-12-05 05:10 */
			'datetime'		=> '/^$|^(\d{4}|\d{2})[-\/]\d{2}[-\/]\d{2} \d{2}:\d{2}$/',
			/* selection: truth for any string (like 'open' but forcing length 1+) (for select combos) */
			'selection'		=> '/^.+$/',
		);

	
/***************
** D E F I N I T I O N S
***************/
		
		public function __construct(){
		
			$this->ruleSets = array();
			
			$ruleSetsDir = win2unix(dirname(__FILE__)).'/'.'ruleSets';
	
			foreach( getDirFiles($ruleSetsDir) as $path ){
				if( substr($path, -10) != '.rules.php' ) continue;
				$setName = basename(substr($path, 0, -10));
				if( is_file($path) && is_array($set=require_once($path)) ){
					$this->ruleSets[$setName] = $set;
				}
			}
			
		}
		
	}

?>