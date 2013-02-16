<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	require_once( dirname(__FILE__).'/Rules.class.php' );
	require_once( dirname(__FILE__).'/Tips.class.php' );

	class Validate{

		private $Rules;				/* Object of Class Rules, to hold definition sets */
		private $expressions;                   /* List of understood regular expressions by codename */

		private $Tips;				/* Object holding tips for the user, for test failure */
		private $fmtTips;			/* Keeps a list of tips for validations that fail on format */
		private $lenTips;			/* Keeps a list of tips for validations that fail on length */

		public function __construct(){

			$this->Rules = new Rules();
			$this->expressions = $this->Rules->expressions;

			$this->Tips = new Tips();
			$this->fmtTips = $this->Tips->format;
			$this->lenTips = $this->Tips->length;

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

		public function getRuleSet( $sSet ){
			return isset($this->Rules->ruleSets[$sSet]) ? $this->Rules->ruleSets[$sSet] : array();
		}

		public function test( $aData, $sSet, $strict=false ){
			return $this->testRules( $aData, $this->getRuleSet($sSet), $strict );
		}


/***************
** P R I V A T E   M E T H O D S
***************/

		private function testRules($set, $ruleSet, $strict=false){

			# If strict, validate all data received has defined rules
			if( $strict ){
				foreach( $set as $field => $value ){
					if( !isset($this->Rules->$ruleSet[$field]) ){
						return trigger_error(
							"El campo {$field} no pertenece al set de reglas establecido.",
							E_USER_NOTICE
						);
					}
				}
			}
			# Check each field using the set of rules
			foreach( $ruleSet as $field => $rule ){
				if( !isset($set[$field]) ){
					if( !$strict ) continue;
					else return trigger_error(
						"El campo {$field} está en las reglas pero no en el set recibido.",
						E_USER_NOTICE
					);
				}
				$val = $set[$field];
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
			return true;
		}

		/**
		 * This function looks for the right tip (in Tips class) and returns a
		 * formatted answer. It handles errors in format validation. $rng means
		 * length has both limits. If $l == $u then both exist and are equal
		 */
		private function fmtFailed($field, $exprType)
		{
			return array(
				'tip'		=> $this->fmtTips[$exprType],
				'field'		=> $field,
				'errType'	=> 'fmt',
			);
		}

		/**
		 * This function looks for the right tip (in Tips class) and returns a
		 * formatted answer. It handles errors in length validation. $rng means
		 * length has both limits. If $l == $u then both exist and are equal
		 */
		private function lenFailed($field, $l, $u, $rng=false)
		{
			$key = ($l == $u) ? '000' : (int)!!$rng.(int)!!$l.(int)!!$u;

			return array(
				'tip'		=> sprintf($this->lenTips[$key], $l, $u),
				'field'		=> $field,
				'errType'	=> 'len',
			);
		}

	}