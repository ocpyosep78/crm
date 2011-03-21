<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	function updateCRM(){
		
		if( !getSes('id_profile') == 1 ) return oPermits()->noAccessMsg();
		
		$path = realpath('update.bat');
		if( !$path ) return showStatus('update batch missing');
		
		$command = "start /b \"{$path}\"";
		test( passthru($command) );
		
		
		return showStatus('ya va...');
		
	}


	function loadFunctionFiles(){
		
		# Auto load functions scripts (all files within FUNCTIONS_PATH directory)
		if( is_dir(FUNCTIONS_PATH) && ($dir=dir(FUNCTIONS_PATH)) ){
			while( $name=$dir->read() ){
				if( $name == '.' || $name == '..' ) continue;
				if( is_dir($file=FUNCTIONS_PATH.$name) ) continue;
				require_once( $functions[]=$file );
			}
			$dir->close();
		}
		else trigger_error('Error al iniciar aplicación.', E_USER_ERROR);
	
	}
	
	function loadMainSmartyVars(){
	
		# Put main objects in Smarty's universe
		oSmarty()->assign('Builder', $GLOBALS['Builder']);
		oSmarty()->assign('Permits', oPermits());
		
		# Global Smarty vars
		oSmarty()->assign('APP_NAME', APP_NAME);
		oSmarty()->assign('APP_IMG', APP_IMG);
		oSmarty()->assign('CHAT_ADDRESS', CHAT_ADDRESS);
		oSmarty()->assign('IMG_PATH', IMG_PATH);
		oSmarty()->assign('IN_FRAME', oNav()->inFrame ? 1 : 0);
		oSmarty()->assign('LAST_UPDATE', strtotime(LAST_UPDATE));
		oSmarty()->assign('PROFILE', getSes('profile'));
		oSmarty()->assign('TODAY', date('Y-m-d') );
		oSmarty()->assign('URL', "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
		oSmarty()->assign('USER', getSes('user'));
		oSmarty()->assign('USER_NAME', getSes('name').' '.getSes('lastName'));
		oSmarty()->assign('VERSION', VERSION);
		oSmarty()->assign('VERSION_STATUS', VERSION_STATUS);
		
		oSmarty()->assign('cycleValues', '#eaeaf5,#e5e5e5,#e5e5e5');
	
		oSmarty()->assign('DATES', array(
			'today'		=> date('Y-m-d h:i:s'),
			'nextWeek'	=> date('Y-m-d h:i:s', strtotime('+ 7 days')),
			'nextMonth'	=> date('Y-m-d h:i:s', strtotime('+ 1 month')),
		) );
		
	}

	function test($something, $moveOn=false){
	
		if( !is_bool($moveOn) || count(func_get_args()) > 2 ){
			$moveOn = false;
			$something = func_get_args();
		}
		
		ob_start();
		echo "\n/* DEBUGGER */\n\n";
		$something ? print_r( $something ) : var_dump( $something );
		$text = preg_replace('/\n*$/', '', ob_get_contents());
		ob_end_clean();
		
		if( !isXajax() ) echo '<pre>'.nl2br($text).'</pre>';
		else alertThroughXajax( $text );
		
		if( !$moveOn ) die();
		
	}
	
	function alertThroughXajax( $text ){
	
		oXajaxResp();		/* Let Builder include required files */
		$resp = new xajaxResponse();
		$resp->addAlert( $text );
		
		returnXajax( $resp );
		
	}
	
	function returnXajax( $resp=NULL ){
	
		if( is_null($resp) ) $resp = oXajaxResp();
		
		header( "Content-type: text/xml; charset=iso-8859-1" );
		print $resp->getXML();
		die();
		
	}
	
	function toJson( $arr=array() ){
		
		if( !is_array($arr) || !count($arr) ) return '{}';
		foreach( $arr as $k => $v ){
			$json[] = "'{$k}':".(is_array($v) ? toJson($v) : (is_numeric($v) ? $v : "'".addslashes($v)."'"));
		}
		
		return '{'.join(",", $json).'}';
	
	}
	
	function getSkinName(){
		return isset($_GET['skin']) ? $_GET['skin'] : (defined('SKIN') && SKIN ? SKIN : NULL);
	}
	
	function getSkinTpl(){
	
		$skinApplied = ($skin=getSkinName()) && is_file($tpl=SKINS_PATH."{$skin}.tpl");
		
		return '../../'.($skinApplied ?  $tpl : CORE_PATH.'main.tpl');
		
	}
	
	function getSkinCss(){
	
		$skin = getSkinName();
		return $skin && is_file($css=SKINS_PATH."{$skin}.css") ? $css : CORE_PATH.'style.css';
		
	}
	
	function safeDiv( $a , $b , $def=0 ){
		return $b ? $a/$b : $def;
	}
	
	function getPercent($val, $total, $dec=0) {
		return ($total) ? round($val * 100 / $total, $dec) : 0;
	}
		
	function win2unix( $path ){
		return str_replace( '\\', '/', $path );
	}
	
	function isWinOS(){
		return !!( strtoupper(substr(PHP_OS,0,3)) === 'WIN' );
	}
	
	function checkTime( $str ){
		return !!preg_match('/^(2[0-3]|[01]\d):[0-5]\d$/', $str);
	}
	
	function canonicalize_time( $time ){
		return preg_match('/[\s0]*(\d|1[0-2]):(\d{2})\s*([AaPp][Mm])/xms', $time, $match)
			? sprintf('%02d:%d%s', $match[1], $match[2], strtoupper($match[3]))
			: false;
	}
	
	function format_time($h, $m=NULL ){
		$time = !$m ? (strstr($h, ':') ? $h : "{$h}:00") : "{$h}:{$m}";
		preg_match('/^(2[0-3]|[01]?\d):([0-5]?\d)$/xms', $time, $m);
		return preg_match('/^(2[0-3]|[01]?\d):([0-5]?\d)$/xms', $time, $match)
			? sprintf('%02d:%02d', $match[1], $match[2])
			: false;
	}
	
	function format_date($y, $m, $d){
		return preg_match('/[^\d]+/', $y.$m.$d) ? false : date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
	}
	
	function checkTimeStamp( $str='' ){
	
		return date('Y-m-d H:i:s', strtotime($str)) === $str;
		
	}
	
	function mySqlDate( $time=NULL ){
		
		return date('Y-m-d H:i:s', $time);
		
	}
			
	/**
	 * Uses array $a2 keys to sort matching keys in $a1.
	 * For example:
	 *	$a1 = array('one' => 'uno', 'two' => 'dos', 'three' => 'tres', 'four' => 'cuatro');
	 *	$a2 = array('two' => 'anything', 'one' => 'does not matter', 'four' => NULL);
	 *	array_sort_keys($a1, $a1);
	 *	# Now $a1 is:
	 *		array('two' => 'dos', 'one' => 'uno', 'four' => 'cuatro', 'three' => 'tres')
	 */
	function array_sort_keys($a1, $a2){
	
		foreach( array_intersect_key($a2, $a1) as $k => $v ) $new[$k] = isset($a1[$k]) ? $a1[$k] : NULL;
		foreach( array_diff_key($a1, $a2) as $k => $v ) $new[$k] = $v;
		
		return ($a1 = isset($new) ? $new : $a1);
		
	}

?>