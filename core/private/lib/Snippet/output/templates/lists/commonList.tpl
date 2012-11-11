<div class='commonListWrapper'>
  <div class='listTitles'>
	{foreach from=$fields key=field item=atts}
	  {if $field && $atts.name && not $atts.hidden}
		<div for='{$field}' title='{$atts.name}'>{$atts.name|truncate:20:'...'}</div>
	  {/if}
	{/foreach}

	<div class='tablesearch' style='text-align:right;'>
		<input type='text' />
	</div>
  </div>

  <div style='clear:both;'></div>

  <div class='commonListInnerWrapper'>
	<div class='listWrapper' id='lw_{$groupId}'>
		<div class='listPreLoad'>
			cargando la lista, espere por favor...
			<img src='{$SNIPPET_IMAGES}/timer.gif' />
		</div>
	</div>
  </div>
</div>