<div id='main_navBar'>
	<div id='navButtonsBar'>
	  {if $USER}
		{foreach from=$pagestate.tree.modules item=module}
		  &nbsp;&nbsp;
		  <a href='{$BBURL}/{$module.uri}' rel='{$module.mainpage}'
			class='navMod{if $module.id == $pagestate.tree.module} navCurrMod{/if}'><img
			src="{$URL_UPLOADS}/modules/{$module.image}"
			alt="{$module.module}" title="{$module.module}" /></a>
		{/foreach}
	  {/if}
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
		<span>página</span>
		<span>/&gt; <em><a href='{$BBURL}/{$pagestate.page.uri}'
		  rel='{$pagestate.page.id}'>{$pagestate.page.alias}</a></em></span>
	  {/if}
	</div>

	{if $USER}<img class="logout" alt="Salir" title="Salir" src="{$img_logout}" />{/if}

	<div class='transparent-grad'></div>
</div>