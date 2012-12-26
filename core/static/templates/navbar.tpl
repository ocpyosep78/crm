<div id='main_navBar'>
	<div id='navButtonsBar'>
	  {if $USER}
		{foreach from=$pagestate.areas key=area item=btn}
		  &nbsp;&nbsp;
		  <a href='{$BBURL}/{$area}'
			class='navMod{if $area == $pagestate.areaid} navCurrMod{/if}'><img
			src="{$BBURL}/{$btn.image}" alt="{$btn.name}" title="{$btn.name}" /></a>
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
		<span>ruta</span>
		<span>/&gt; <em><a href='javascript:void(0)'>{$pagestate.page.name}</a></em></span>
	  {/if}
	</div>

	{if $USER}<img class="logout" alt="Salir" title="Salir" src="{$img_logout}" />{/if}

	<div class='transparent-grad'></div>
</div>