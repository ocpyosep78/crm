<div id='main_navBar'>
	<div id='navButtonsBar'>
	  {if $USER}
		{foreach from=$pagestate.tree.modules item=module}
		  &nbsp;&nbsp;
		  <a href='{$BBURL}/{$module.uri}' rel='{$module.id}'
			class='navMod{if $module.id == $pagestate.tree.module.id} navCurrMod{/if}'><img
			src="{$URL_UPLOADS}/pages/{$module.image}"
			alt="{$module.m_alias}" title="{$module.m_alias}" /></a>
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
		<span>/&gt; <em><a href='javascript:void(0)'>{$pagestate.info.alias}</a></em></span>
	  {/if}
	</div>

	{if $USER}<img class="logout" alt="Salir" title="Salir" src="{$img_logout}" />{/if}

	<div class='transparent-grad'></div>
</div>