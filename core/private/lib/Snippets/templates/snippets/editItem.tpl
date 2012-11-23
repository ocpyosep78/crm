{if !$inDialog}
  {$bigTools}
  {$comboList}
{/if}

<div class='snp_item'>
  {if !$inDialog}<div>{$title}</div>{/if}

  {if !empty($data.__disabled__)}<h3>eliminado</h3>{/if}

  {foreach from=$chunks item=chunk}
	<div class='snp_chunk{if !empty($data.__disabled__)} snp_removed{/if}'>
	  <table>
		{foreach from=$chunk key=field item=item}
		  {if !preg_match('#^__.+__$#', $field)}
			<tr>
			  <th>{if $item.name}{$item.name}{/if}</th>
			  <td>
				<input type='text' value='{$item.value}' />
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