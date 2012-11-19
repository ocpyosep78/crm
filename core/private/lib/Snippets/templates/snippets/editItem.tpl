<div class='snp_item'>
  {if !$inDialog}
	{if $objectID}
	  <span>Editar {$name}{if $DEVMODE} <strong>(objectID: {$objectID})</strong>{/if}</span>
	{else}
	  <span>Ingresar nuev{if $gender == 'm'}o{else}a{/if} {$name}</span>
	{/if}
  {/if}

  {foreach from=$chunks item=chunk}
	<div>
	  <table>
		{foreach from=$chunk key=field item=data}
		  <tr>
			<th>{$data.name}</th>
			<td>
			  <input type='text' value='{$data.value}' />
			</td>
		  </tr>
		{/foreach}
	  </table>
	</div>
  {foreachelse}
	<div class='emptyFieldMsg'>Error: no se encontró información de este elemento</div>
  {/foreach}

  {if isset($snippet_extraHTML)}{$snippet_extraHTML}{/if}
</div>