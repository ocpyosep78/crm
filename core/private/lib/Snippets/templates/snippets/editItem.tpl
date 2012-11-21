<div class='snp_item'>
  {if !$inDialog}<div>{$title}</div>{/if}

  {if $disabled}<h3>ELIMINADO</h3>{/if}

  {foreach from=$chunks item=chunk}
	<div class='snp_chunk{if $disabled} snp_item_removed{/if}'>
	  <table>
		{foreach from=$chunk key=field item=item}
		  <tr>
			<th>{$item.name}</th>
			<td>
			  <input type='text' value='{$item.value}' />
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