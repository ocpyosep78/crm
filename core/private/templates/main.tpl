<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

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
	
	{* Xajax *}
	{$Xajax->printJavascript()}
	
	{* javaScript files *}
	<script type='text/javascript'>window.IN_FRAME = {$IN_FRAME}</script>
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

	<div id='main_navBar'>
	
		<div id='navButtonsBar'>
		  {foreach from=$Page->navButtons key=key item=item}
			&nbsp;&nbsp;
			<a href='javascript:void(0);'><img class='{if $key == $Page->module}navCurrMod{else}navMod{/if}'
				src="{$IMG_PATH}navButtons/{$key}.png" alt="{$item.name}" title="{$item.name}"
				onclick='switchNav(event, this); {if $item.action}{$item.action};{/if}' /></a>
		  {/foreach}
		</div>
		
		<div id='loggedAs'>
		  {if $USER}
			<span>usuario:</span> {$USER}
				&nbsp;&nbsp;|&nbsp;&nbsp;
			<span>perfil:</span> {$PROFILE}
		  {/if}
		</div>
		
		<div id='navSteps'>
		  {if $USER}
			<span>ruta</span>
			{foreach from=$Page->pageNav key=key item=action}
			  <span>/&gt;</span>
			  <a href='javascript:void(0)'{if $action} onclick="getPage(event, '{$action}')"{/if}>{$key}</a>
			{/foreach}
		  {/if}
		</div>
		
	</div>
	
	<div id='main_logoArea' colspan="2">
		<img src="{$Page->appImg}" />
		<span>{$Page->appName}</span>
		{include file='widgets/bigTools.tpl'}
	</div>
			
			
		</tr>
	
	<table id='main_container' border='0' cellpadding="1">
		<tr>
		
			<td id='main_menu' rowspan="2">
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
			</td>
		
			<td id='main_box'>
				{$Page->content}
			</td>
			
			
		</tr>
		<tr>
		
		
			<td id='main_foot' colspan="2">
				<hr class='sep' />
				<span>customer relationship management / ingetec </span>v{$VERSION}
				{$VERSION_STATUS}
				&nbsp;&nbsp;&nbsp;&nbsp;
				copyright <a href='mailto:diego.bindart@gmail.com'>dbarreiro</a>
				{$LAST_UPDATE|date_format:"%d-%m-%Y"}
			</td>
			
			
		</tr>
	</table>
	
	
	<div id='curtain'></div>		{* modal windows, refer to JS:Modal object *}
	
	<div id='xajax_addElement'></div>
	
	{* Widgets *}
	{if $USER && $Permits->can('agenda')}{include file='widgets/eventInfo.tpl'}{/if}
	{if $USER && $Permits->can('chatActivity')}{include file='widgets/chat.tpl'}{/if}
	{if $USER}{include file='widgets/alerts.tpl'}{/if}
	{include file='widgets/statusMsgs.tpl'}
	{include file='widgets/loadingMsg.tpl'}
	{if $Page->debugger}{include file='widgets/debugger.tpl'}{/if}
	
	
</body>
</html>