<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	function say($msg, $type='', $img=''){
		return addScript("say('{$msg}', '{$type}', '{$img}');");
	}
	
	function returnSearchResults($tgt, $tpl, $uID){
		addAssign('TableSearchCache', 'innerHTML', oSmarty()->fetch($tpl));
		addScript("TableSearch.showResults('{$uID}');");
		return addScript("\$('{$tgt}').update();");
	}
	
	function showMenu(){
		isXajax() ? addScriptCall('showMenu') : oPageCfg()->add_jsOnLoad("showMenu();");
	}
	
	function hideMenu(){
		isXajax() ? addScriptCall('hideMenu') : oPageCfg()->add_jsOnLoad("hideMenu();");
	}
	
	// Xajax Shortcuts
	function isXajax( $call=NULL ){
		return isset($_POST['xajax']) && ($call ? $_POST['xajax'] == $call : true);
	}
	
	function addAlert( $x ){
		oXajaxResp()->addAlert( $x );
		return oXajaxResp();
	}
	
	function addAssign($x, $y, $z){
		oXajaxResp()->addAssign($x, $y, $z);
		return oXajaxResp();
	}
	
	function addAppend($x, $y, $z){
		oXajaxResp()->addAppend($x, $y, $z);
		return oXajaxResp();
	}
	
	function addElement($id, $content=''){
		addAssign('xajax_addElement', 'innerHTML', "{$content}");
		return addScript("\$('{$id}').adopt( \$('xajax_addElement').getChildren() );" );
		return addScript("xajax_addElement('{$id}');");
	}
	
	function addScript( $x ){
		oXajaxResp()->addScript( $x );
		return oXajaxResp();
	}
	
	function addScriptCall(){
		$arr = func_get_args();
		call_user_func_array(array(oXajaxResp(), 'addScriptCall'), $arr);
		return oXajaxResp();
	}