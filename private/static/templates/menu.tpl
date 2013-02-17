<div id='main_menu'>
	<img id='hideMenu' src='{$IMAGES_URL}/arrow_head_left.gif' title='Ocultar menú' />
	<img id='showMenu' src='{$IMAGES_URL}/arrow_head_right.gif' title='Mostrar menú' />

	<div id='menuDiv'>
		<div class='h_filler' style='width:140px;'>&nbsp;</div>

		{foreach from=$pagestate.groups item=group}
			<div class='menuGroup'>{$group.area}</div>

			{foreach from=$group.pages key=pageid item=page}
				{if $page.module == $pagestate.areaid}
					<div class='menuItem' for='{$pageid}'>
						<a href='{$BBURL}/{$pageid}'>{$page.name}</a>
					</div>
				{/if}
			{/foreach}
		{/foreach}
	</div>
</div>