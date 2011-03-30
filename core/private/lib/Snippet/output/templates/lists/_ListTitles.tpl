<div class='listTitles'>

	{foreach from=$fields key=field item=atts}
	  {if $field && $atts.name && not $atts.hidden}
		<div for='{$field}'>{$atts.name}</div>
	  {/if}
	{/foreach}
	
	<div for='tools'></div>
	
</div>