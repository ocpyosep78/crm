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
	<link rel="stylesheet" type="text/css" href="core/private/scripts/jquery/themes/{$jQueryUiTheme}/jquery-ui.css" />

	{foreach from=$Page->styleSheets item=styleSheet}
		<link rel="stylesheet" type="text/css" href="{$styleSheet}" />
	{/foreach}


	{* JS files *}
	<script type='text/javascript' src='core/private/scripts/jquery/jquery-1.8.2.min.js'></script>
	<script type='text/javascript' src='core/private/scripts/jquery/jquery-ui-1.9.1.min.js'></script>

	<script type='text/javascript'>window.IN_FRAME = {$IN_FRAME}</script>
	<script type='text/javascript' src='{$core_scripts}common.js'></script>

	{foreach from=$Page->jScripts item=jScript}
		<script type='text/javascript' src='{$jScript}'></script>
	{/foreach}

	{* JS code *}
	<script type='text/javascript'>
		{$Page->jsCode}
		J(function(){literal}{{/literal}
			{$Page->jsOnLoad}
		{literal}}{/literal} );
	</script>

	{* Ajax *}
	{$Xajax->printJavascript()}

</head>
<body>


	<div id='frameCover'></div>


	<div id='frameContent'>

	  <div id='frameTitle'>
		{$Page->page}
		<img class='CloseButtonHover' src="app/images/buttons/closeHover.png" alt='cerrar' title='cerrar ventana' />
		<img id='FrameCloseBtn' class='CloseButton' src="app/images/buttons/close.png" alt='cerrar' />
	  </div>
	  <div id='frameArea'>
		<div id='notifications'>
			<img class='errorStatus' src='app/images/statusMsg/error.png'>
			<img class='successStatus' src='app/images/statusMsg/success.png'>
			<img class='warningStatus' src='app/images/statusMsg/warning.png'>
			<div></div>
		</div>
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