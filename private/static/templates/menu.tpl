<div id='main_menu'>
	<img id='hideMenu' src='{$IMAGES_URL}/arrow_head_left.gif' title='Ocultar menú' />
	<img id='showMenu' src='{$IMAGES_URL}/arrow_head_right.gif' title='Mostrar menú' />

	<div id='menuDiv'>
		<div class='h_filler' style='width:140px;'>&nbsp;</div>

		{foreach from=$pagestate.tree.menu key=area item=pages}
			<div class='menuGroup'>{$area}</div>

			{foreach from=$pages item=page}
				<div class='menuItem' for='{$page.id}'>
					<a href='{$BBURL}/{$page.uri}'>{$page.alias}</a>
				</div>
			{/foreach}
		{/foreach}
	</div>
</div>