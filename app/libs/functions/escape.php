<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	function escaped_nl2br( $str ){
	
		return str_replace(array('\r\n', '\n', '\r'), '<br />', $str);
		
	}

	function escapebycharacter( $char ){
	
		if( $char == '+' ) { return '%20'; }
		if( $char == '%2A' ) { return '*'; }
		if( $char == '%2B' ) { return '+'; }
		if( $char == '%2F' ) { return '/'; }
		if( $char == '%40' ) { return '@'; }
		if( $char == '%80' ) { return '%u20AC'; }
		if( $char == '%82' ) { return '%u201A'; }
		if( $char == '%83' ) { return '%u0192'; }
		if( $char == '%84' ) { return '%u201E'; }
		if( $char == '%85' ) { return '%u2026'; }
		if( $char == '%86' ) { return '%u2020'; }
		if( $char == '%87' ) { return '%u2021'; }
		if( $char == '%88' ) { return '%u02C6'; }
		if( $char == '%89' ) { return '%u2030'; }
		if( $char == '%8A' ) { return '%u0160'; }
		if( $char == '%8B' ) { return '%u2039'; }
		if( $char == '%8C' ) { return '%u0152'; }
		if( $char == '%8E' ) { return '%u017D'; }
		if( $char == '%91' ) { return '%u2018'; }
		if( $char == '%92' ) { return '%u2019'; }
		if( $char == '%93' ) { return '%u201C'; }
		if( $char == '%94' ) { return '%u201D'; }
		if( $char == '%95' ) { return '%u2022'; }
		if( $char == '%96' ) { return '%u2013'; }
		if( $char == '%97' ) { return '%u2014'; }
		if( $char == '%98' ) { return '%u02DC'; }
		if( $char == '%99' ) { return '%u2122'; }
		if( $char == '%9A' ) { return '%u0161'; }
		if( $char == '%9B' ) { return '%u203A'; }
		if( $char == '%9C' ) { return '%u0153'; }
		if( $char == '%9E' ) { return '%u017E'; }
		if( $char == '%9F' ) { return '%u0178'; }
		
		return $char;
		
	}
	
	function escape( $string ){
	
		$result = "";
		for( $i=0 ; $i<strlen($string) ; $i++ ) $result .= escapebycharacter(urlencode($string[$i]));
		
		return $result;
		
	}
	
	function unescapebycharacter( $str ){
	
	/*
	PHP URL encoding/decoding functions for Javascript interaction V3.0
	(C) 2006 www.captain.at - all rights reserved
	License: GPL
	*/
	
		$char = $str;
	
		if( $char == '%u20AC' ) { return array( "%80" , 5 ); }
		if( $char == '%u201A' ) { return array( "%82" , 5 ); }
		if( $char == '%u0192' ) { return array( "%83" , 5 ); }
		if( $char == '%u201E' ) { return array( "%84" , 5 ); }
		if( $char == '%u2026' ) { return array( "%85" , 5 ); }
		if( $char == '%u2020' ) { return array( "%86" , 5 ); }
		if( $char == '%u2021' ) { return array( "%87" , 5 ); }
		if( $char == '%u02C6' ) { return array( "%88" , 5 ); }
		if( $char == '%u2030' ) { return array( "%89" , 5 ); }
		if( $char == '%u0160' ) { return array( "%8A" , 5 ); }
		if( $char == '%u2039' ) { return array( "%8B" , 5 ); }
		if( $char == '%u0152' ) { return array( "%8C" , 5 ); }
		if( $char == '%u017D' ) { return array( "%8E" , 5 ); }
		if( $char == '%u2018' ) { return array( "%91" , 5 ); }
		if( $char == '%u2019' ) { return array( "%92" , 5 ); }
		if( $char == '%u201C' ) { return array( "%93" , 5 ); }
		if( $char == '%u201D' ) { return array( "%94" , 5 ); }
		if( $char == '%u2022' ) { return array( "%95" , 5 ); }
		if( $char == '%u2013' ) { return array( "%96" , 5 ); }
		if( $char == '%u2014' ) { return array( "%97" , 5 ); }
		if( $char == '%u02DC' ) { return array( "%98" , 5 ); }
		if( $char == '%u2122' ) { return array( "%99" , 5 ); }
		if( $char == '%u0161' ) { return array( "%9A" , 5 ); }
		if( $char == '%u203A' ) { return array( "%9B" , 5 ); }
		if( $char == '%u0153' ) { return array( "%9C" , 5 ); }
		if( $char == '%u017E' ) { return array( "%9E" , 5 ); }
		if( $char == '%u0178' ) { return array( "%9F" , 5 ); }
		
		$char = substr($str, 0, 3);
		if( $char == "%20") { return array("+", 2); }
		
		$char = substr($str, 0, 1);
	
		if( $char == '*' ) { return array( "%2A", 0 ); }
		if( $char == '+' ) { return array( "%2B", 0 ); }
		if( $char == '/' ) { return array( "%2F", 0 ); }
		if( $char == '@' ) { return array( "%40", 0 ); }
		
		return ( $char == "%" ) ? array(substr($str, 0, 3), 2) : array($char, 0);
		
	}
	
	function unescape( $string ){
	
	/*
	PHP URL encoding/decoding functions for Javascript interaction V3.0
	(C) 2006 www.captain.at - all rights reserved
	License: GPL
	*/
		
		$result = "";
		
		for( $i=0 ; $i<strlen($string) ; $i++ ){
		
			$decstr = "";
			
			for ($p = 0; $p <= 5; $p++) $decstr .= isset($string[$i+$p])?$string[$i+$p]:'';
			
			list( $decodedstr , $num ) = unescapebycharacter( $decstr );
			$result .= urldecode( $decodedstr );
			$i += $num;
			
		}
		
		return $result;
		
	}