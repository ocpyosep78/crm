<?php

function say($msg, $type='', $img=''){
	return addScript("say('{$msg}', '{$type}', '{$img}');");
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

function addScript( $x ){
	oXajaxResp()->addScript( $x );
	return oXajaxResp();
}

function addScriptCall(){
	$arr = func_get_args();
	call_user_func_array(array(oXajaxResp(), 'addScriptCall'), $arr);
	return oXajaxResp();
}

function addElement($content='', $selector='body', $hidden=true){
	$hide = $hidden ? 'true' : 'false';
	addAssign('importedElement', 'innerHTML', "{$content}");
	return addScript("importElement('{$selector}', {$hide});");
}

/**
 * dialog(string $content, string $element[, array $atts])
 *      Creates $element if it doesn't exist, make $content it's inner html, and
 * call jQuery-ui dialog() on it.
 *
 * @param string $content       Template name (ending on '.tpl') or html
 * @param string $element       Valid jQuery selector for an id (including #)
 * @param array $atts           List of properties to be passed to dialog()
 * @return XajaxResponse
 */
function dialog($content, $element, $atts=array())
{
	addScript("\$('{$element}').empty()");

	// Send the html (fetch the template first, if $content's a template name)
	$isTemplate = preg_match('_\.tpl$_', $content);
	$html = $isTemplate ? oSmarty()->fetch($content) : $content;
	addElement($html, $element, true);

	$cfg = toJson((array)$atts, true);

	addScript("\$('{$element}').dialog({$cfg})");

	return addScript("$('.ui-widget-overlay').click(function(){
		\$('{$element}').dialog('close');
	});");
}