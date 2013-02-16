{if !$inDialog}
  {$bigTools}
  {$comboList}
{/if}

<div class='snp_item{if $data.__disabled__} snp_disabled{/if}'>
  {foreach from=$chunks item=chunk}
	<div class='snp_chunk{if !empty($data.__disabled__)} snp_disabled{/if}'>
	  <table>
		{foreach from=$chunk key=field item=item}
		  {if !preg_match('#^__.+__$#', $field)}
			<tr>
			  <th>{if $item}{$item.name}{/if}</th>
			  <td>
				{if $item}
				  <input type='text' value='{$item.value}' />
				{/if}
			  </td>
			</tr>
		  {/if}
		{/foreach}
	  </table>
	</div>
  {foreachelse}
	<div class='emptyFieldMsg'>Error: no se encontró información de este elemento</div>
  {/foreach}

  {if isset($snippet_extraHTML)}{$snippet_extraHTML}{/if}
</div>