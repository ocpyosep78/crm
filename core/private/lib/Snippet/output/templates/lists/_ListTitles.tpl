<div class='listTitles'>

	{foreach from=$fields key=field item=atts}
	  {if $field && $atts.name && not $atts.hidden}
		<div for='{$field}' title='{$atts.name}'>{$atts.name|truncate:20:'...'}</div>
	  {/if}
	{/foreach}
	
	<div for='*'>Búsqueda global</div>
	
</div>