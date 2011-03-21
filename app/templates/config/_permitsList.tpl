<div class='permitsFilter'>
	&nbsp;
	{foreach from=$permitsFilters key=code item=permit}
		<span for='{$code}'{if $permitsFilter eq $code} class='selectedPermitsFilter'{/if}>{$permit}</span>
	{/foreach}
	&nbsp;
	<h5{if $stat eq 0} class='pendingPermit'{/if}>
		Permisos {if $stat eq 0}no{/if} asignados
	</h5>
</div>

<div class='permitsList'>

	<input type='hidden' class='permitStat' value='{$stat}' />

	{foreach from=$permits item=permit}
	  {if $permit.enabled == $stat}
	  
		{if $permit.type == 'page'}{assign var=type value='página'}
		{elseif $permit.type == 'module'}{assign var=type value='módulo'}
		{else}{assign var=type value='otros'}{/if}
		
		<div class='permitRow' for='{$permit.code}' title='{$permit.title}'>
			<div class='permitName'>{$permit.name}</div>
			<div>{$type}</div>
		</div>
		
	  {/if}
	{/foreach}
	
</div>