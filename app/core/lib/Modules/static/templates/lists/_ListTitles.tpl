<div class='listTitles'>

	{foreach from=$fields item=field}
	  {if $field && $fieldsCfg.$field.name && not $fieldsCfg.$field.hidden}
		{assign var=fieldName value=$fieldsCfg.$field.name}
		<div for='{$field}'>{$fieldName}</div>
	  {/if}
	{/foreach}
	
	<div for='tools'></div>
	
</div>