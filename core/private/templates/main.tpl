<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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

	<title>{$Page->appTitle}</title>


	{* Styles *}
	<link rel="stylesheet" type="text/css" href="{$core_scripts}/jquery/themes/{$jQueryUiTheme}/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="{$core_scripts}/jquery/jquery.qtip2.css" />

	{foreach from=$Page->styleSheets item=styleSheet}
		<link rel="stylesheet" type="text/css" href="{$styleSheet}" />
	{/foreach}


	{* JS files *}
	<script type="text/javascript" src="{$core_scripts}/jquery/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="{$core_scripts}/jquery/jquery-ui-1.9.1.min.js"></script>
	<script type="text/javascript" src="{$core_scripts}/jquery/jquery.qtip2.js"></script>

	<script type="text/javascript">window.IN_FRAME = {$IN_FRAME}</script>
	<script type="text/javascript" src="{$core_scripts}/common.js"></script>

	{foreach from=$Page->jScripts item=jScript}
		<script type="text/javascript" src="{$jScript}"></script>
	{/foreach}

	{* JS code *}
	<script type="text/javascript">
		{$Page->jsCode}
		$(function(){literal}{{/literal}
			{$Page->jsOnLoad}
		{literal}}{/literal} );
	</script>

	{* Ajax *}
	{$Xajax->printJavascript()}

</head>
<body>

  {if $IN_FRAME}

	<div id='frameCover'></div>

	<div id='frameContent'>
	  <div id='frameTitle'>
		{$Page->page}
		<img class='CloseButtonHover' src="app/images/buttons/closeHover.png" alt='cerrar' title='cerrar ventana' />
		<img id='FrameCloseBtn' class='CloseButton' src="app/images/buttons/close.png" alt='cerrar' />
	  </div>
	  <div id='frameArea'>

  {else}

	<div id='main_navBar'>
		<div id='navButtonsBar'>
		  {foreach from=$Page->navButtons key=key item=item}
			&nbsp;&nbsp;
			<a href='javascript:void(0);'><img class='{if $key == $Page->module}navCurrMod{else}navMod{/if}'
				src="{$IMG_PATH}/navButtons/{$key}.png" alt="{$item.name}" title="{$item.name}"
				onclick='switchNav(event, this); {if $item.action}{$item.action};{/if}' /></a>
		  {/foreach}
		</div>

		{if $USER}
		  <div id='loggedAs'>
			<span userid='{$USERID}'>usuario: <em>{$USER}</em></span>
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<span>perfil: <em>{$PROFILE}</em></span>
		  </div>
		{/if}

		<div id='navSteps'>
		  {if $USER}
			<span>ruta</span>
			{foreach from=$Page->pageNav key=key item=action}
			  <span>/&gt;
				<em><a href='javascript:void(0)'{if $action} onclick="getPage(event, '{$action}')"{/if}>{$key}</a></em>
			  </span>
			{/foreach}
		  {/if}
		</div>

		<div class='transparent-grad'></div>
	</div>

  {/if}

	<div id='notifications'>
		<span>{$Page->appName}</span>
		<img class='errorStatus' src='app/images/statusMsg/error.png'>
		<img class='successStatus' src='app/images/statusMsg/success.png'>
		<img class='warningStatus' src='app/images/statusMsg/warning.png'>
		<div></div>
	</div>

	<div id='main_box'>{$Page->content}</div>

  {if $IN_FRAME}

	  </div>
	</div>

  {else}

	<div id='main_foot'>
		<hr class='sep' />
		<span>customer relationship management / ingetec </span>v{$VERSION}
		{$VERSION_STATUS}
		&nbsp;&nbsp;&nbsp;&nbsp;
		copyright <a href='mailto:diego.bindart@gmail.com'>dbarreiro</a>
		{$LAST_UPDATE|date_locale:'d/m/Y'}
	</div>

	<div id='main_menu' rowspan="2">
		<img id='hideMenu' src='app/images/arrow_head_left.gif' title='Ocultar menú' />
		<img id='showMenu' src='app/images/arrow_head_right.gif' title='Mostrar menú' />
		<div id='menuDiv'>
		  <div class='h_filler' style='width:140px;'>&nbsp;</div>
		  {foreach from=$Page->menuItems key=key item=items}
			<div class='menuGroup'>{$key}</div>
			{foreach from=$items item=x}
			  <div class='{if $x.action}menuItem{else}disabledMenuItem{/if}' for='{$x.code}'>
				<a href='javascript:void(0);' {if $x.action}onclick='{$x.action};'{/if}>{$x.name}</a>
			  </div>
			{/foreach}
		  {/foreach}
		</div>
	</div>

  {/if}

	{* Widgets *}
	{if false && !$IN_FRAME && $USER && $Permits->can('chatActivity')}{include file='widgets/chat.tpl'}{/if}
	{if false && !$IN_FRAME && $USER}{include file='widgets/alerts.tpl'}{/if}

	<div id='loadingGif'>
		<div>Cargando...</div>
		<img src="app/images/loading.gif" alt="Cargando..." />
	</div>

	{if $Page->debugger}
	  <div id='debugHeader'>
		<div>
		  <div id="openDebug"></div>
		  <img id='debugStats' src='app/images/stats.gif' alt='extended info' />
		  <div id="debuggerbox" style='float:left; width:400px;'>
			Debugger <span style="color:#0000a0; cursor:pointer;">(click para minimizar)</span>
			<br />
			{foreach from=$Page->develMsgs item=develMsg}{$develMsg}<br />{/foreach}
		  </div>
		</div>
		{if $Page->errMsgs}<hr style='clear:both;' />{/if}
		{foreach from=$Page->errMsgs item=errMsg}{$errMsg}<br />{/foreach}
		<iframe id='debugger'></iframe>
	  </div>
	{/if}


</body>
</html>