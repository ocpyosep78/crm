<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	class Snippet_Validation{
	
/***************
** EXPRESSIONS
***************/

		private $expressions = array(
			/* open: anything (for fields where only length matters) */
			'open'			=> '/.*/',
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
** TIPS
***************/
		
		public $fmtTips = array(
			'open'			=> 'El texto ingresado contiene caracteres inválidos.',
			'text'			=> 'El texto ingresado contiene caracteres inválidos.',
			'alpha'			=> 'Este campo acepta solamente letras, números y guiones bajos.',
			'alphaMixed'	=> 'Este campo acepta letras, números y guiones bajos, y debe tener al menos una letra y un número.',
			'email'			=> 'Debe escribir una dirección de email válida.',
			'num'			=> 'Este campo acepta solamente números.',
			'cost'			=> 'Este campo acepta solamente números enteros o decimales.',
			'docNum'		=> 'Debe ingresar un documento válido (ej: 1234567-8).',
			'phone'			=> 'El teléfono ingresado no es válido.',
			'rut'			=> 'Un RUT válido debe ser un número de 12 dígitos.',
			'ranged'		=> 'Este campo acepta solamente números.',
			'time'			=> 'Debe seleccionar una hora válida.',
			'date'			=> 'Debe seleccionar una fecha válida.',
			'selection'		=> 'Debe seleccionar un elemento de la lista',
		);
		
		public $lenTips = array(	/* Key means, in order: ranged, min is defined, max is defined */
			'101'	=> '%sEl campo debe contener un máximo de %s caracteres.',
			'110'	=> 'El campo debe contener al menos %s caracteres.%s',
			'111'	=> 'La longitud del campo debe estar entre %s y %s.',
			'001'	=> '%sLa longitud del campo no puede ser mayor que %s.',
			'010'	=> 'La longitud del campo no puede ser menor que %s.%s',
			'011'	=> 'Debe escribir al menos %s caracteres y no más de %s.',
			'000'	=> 'Este campo debe contener %s caracteres',
		);
		
		public function __construct(){
			
			foreach( $this->expressions as $code => $expr ){
				if( !isset($this->fmtTips[$code]) ) $this->fmtTips[$code] = '';
			}	/* Initialize all keys to avoid E_NOTICE */
			
		}
		
		public function preProcessInput(&$input, $prefix=' '){
		
			foreach( $input as $key => $val ){
				$isoVal = is_string($val) ? trim($val) : $val;
				$output[preg_replace('/^'.$prefix.'/', '', $key)] = $isoVal;
			}
			
			return isset($output) ? ($input=$output) : $input;
			
		}


/***************
** P R I V A T E   M E T H O D S
***************/

		public function test($data, $rules, $strict=false){
		
			# If strict, validate all data received has defined rules
			if( $strict ) foreach( $data as $field => $value ){
				if( !isset($rules[$field]) ){
					$err = "El campo {$field} no pertenece al set de reglas establecido.";
					return trigger_error($err, E_USER_NOTICE);
				}
			}
			
			# Check each field using the rules-set given
			foreach( $rules as $field => $rule ){
				# If some defined field is not included in data, respond depending on strict param
				if( !isset($data[$field]) && !$strict ) continue;
				elseif( !isset($data[$field]) ){
					$err = "El campo {$field} está en las reglas pero no en el set recibido.";
					return trigger_error($err, E_USER_NOTICE);
				}
				# Explode this field's rule in its parts: type, lower length, upper length
				$val = $data[$field];
				list($type, $l, $u) = (is_array($rule) ? $rule : array()) + array('', NULL, NULL);
				# Specials: ranged
				$len = ($type == 'ranged') ? intval($val) : strlen($val);
				# Check format
				$expr = preg_match('/^\/.*\/$/', $type) ? $type : $this->expressions[$type];
				if( !is_string($val) || !preg_match($expr, $val) ){
					return $this->fmtFailed($field, $type);
				}
				# Check length
				if( $len < $l || ($u && $len > $u) ){
					return $this->lenFailed($field, $l, $u, ($type == 'ranged'));
				}
				# Specials: date
				if( $type == 'date' ){
					$real = strtotime($val);
					$given = str_replace('/', '-', $val);
					if($given != date('y-m-d', $real) && $given != date('Y-m-d', $real)){
						return $this->fmtFailed($field, $type);
					}
				}
			}
			
			# If no error stopped us so far, validation succeeded
			return true;
			
		}
		
		/**
		 * This function looks for the right tip and returns a formatted answer
		 * It handles errors in format validation
		 * $rng means length has both limits
		 * if $l == $u then both limits exist and are equal
		 */
		private function fmtFailed($field, $exprType){
		
			return array(
				'tip'		=> $this->fmtTips[$exprType],
				'field'		=> $field,
				'errType'	=> 'fmt',
			);
		}
		
		/**
		 * This function looks for the right tip (in Tips class) and returns a formatted answer
		 * It handles errors in length validation
		 * $rng means length has both limits
		 * if $l == $u then both limits exist and are equal
		 */
		private function lenFailed($field, $l, $u, $rng=false){
			
			$tip = sprintf($this->lenTips[$l == $u ? '000' : (int)!!$rng.(int)!!$l.(int)!!$u], $l, $u);
			return array(
				'tip'		=> $tip,
				'field'		=> $field,
				'errType'	=> 'len',
			);
		}
		
	}