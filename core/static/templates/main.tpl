<!DOCTYPE html>
<html language="es">
<head>

	<meta http-equiv="X-UA-Compatible" content="IE=8" />
	<!--[if IE 9]>
	  <meta http-equiv="X-UA-Compatible" content="IE=9" />
	<![endif]-->

	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<meta http-equiv="Last-Modified" content="0">
	<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
	<meta name="robots" content="none">

	<link rel="shortcut icon" href="favicon.ico" />

	<title>CRM / Ingetec</title>


	{* Styles *}
	<link rel="stylesheet" type="text/css" href="{$SCRIPTS}/jquery/themes/{$UI_THEME}/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="{$SCRIPTS}/jquery/jquery.qtip2.css" />
	<link rel="stylesheet" type="text/css" href="{$css}" />


	{* JS files *}
	<script type="text/javascript" src="{$SCRIPTS}/jquery/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="{$SCRIPTS}/jquery/jquery-ui-1.9.1.min.js"></script>
	<script type="text/javascript" src="{$SCRIPTS}/jquery/jquery.qtip2.js"></script>
	<script type="text/javascript" src="{$SCRIPTS}/common.js"></script>

	{* JS on domready *}
	{if isset($js)}
	  <script type="text/javascript">
		{foreach from=$js item=code}
			{$code};
		{/foreach}
	  </script>
	{/if}

</head>
<body>

	{include file="$TEMPLATES/navbar.tpl"}

	<div id='notifications'>
		<span>{$APP_NAME}</span>
		<img class='errorStatus' src='app/images/statusMsg/error.png'>
		<img class='successStatus' src='app/images/statusMsg/success.png'>
		<img class='warningStatus' src='app/images/statusMsg/warning.png'>
		<div></div>
	</div>

	<div id='main_box'>{$content}</div>

	{include file="$TEMPLATES/foot.tpl"}

	{include file="$TEMPLATES/menu.tpl"}

	{* Widgets *}
	{if false && $USER && Access::can('chatActivity')}{include file='widgets/chat.tpl'}{/if}
	{if false && $USER}{include file='widgets/alerts.tpl'}{/if}

	<div id='loadingGif'>
		<div>Cargando...</div>
		<img src="app/images/loading.gif" alt="Cargando..." />
	</div>

	{if devMode()}{include file="$TEMPLATES/debugger.tpl"}{/if}

</body>
</html>