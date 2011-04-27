<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

	<meta http-equiv="X-UA-Compatible" content="IE=8" />

	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<meta http-equiv="Last-Modified" content="0">
	<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
	<meta name="robots" content="none">
	
	<link rel="shortcut icon" href="favicon.ico" />

	<title>{$Page->appTitle}</title>
	
	{* Styles *}
	{foreach from=$Page->styleSheets item=styleSheet}
		<link rel="stylesheet" type="text/css" href="{$styleSheet}" />
	{/foreach}
	
	{* Ajax *}
	{$Xajax->printJavascript()}
	{$Pajax->printJavascript()}
	
	{* javaScript files *}
	<script type='text/javascript'>var IN_FRAME = {$IN_FRAME}</script>
	{foreach from=$Page->jScripts item=jScript}
		<script type='text/javascript' src='{$jScript}'></script>
	{/foreach}
	
	{* JS code *}
	<script type='text/javascript'>
		{$Page->jsCode}
		window.addEvent('domready', function(){literal}{{/literal}
			{$Page->jsOnLoad}
		{literal}}{/literal} );
	</script>
	
</head>
<body>


	<div id='frameCover'></div>


	<div id='frameContent'>
		
	  <div id='frameTitle'>
		{$Page->page}
		<img class='CloseButtonHover' src="app/images/buttons/closeHover.png" alt='cerrar' title='cerrar ventana' />
		<img id='FrameCloseButton' class='CloseButton' src="app/images/buttons/close.png" alt='cerrar' />
	  </div>
	  <div id='frameArea'>
		{include file='widgets/statusMsgs.tpl'}
	  	<div id='main_box'></div>
	  </div>
	</div>
	
	
	<div id='curtain'></div>		{* modal windows, refer to JS:Modal object *}
	
	
	{* Widgets *}
	{if $USER && $Permits->can('agenda')}{include file='widgets/eventInfo.tpl'}{/if}
	{include file='widgets/loadingMsg.tpl'}
	{if $Page->debugger}{include file='widgets/debugger.tpl'}{/if}
	

</body>
</html>