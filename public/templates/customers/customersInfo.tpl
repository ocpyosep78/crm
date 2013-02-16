{$comboList}

{if Access::can('editCustomers')}
	<a id='editCustomers' class='infoPageTools' href='javascript:void(0);'
		for='{$custID}'>Editar Cliente</a>
{/if}

<div class='infoBlocks'>
	{foreach from=$blocks item=block}<div>{$block}</div>{/foreach}
</div>