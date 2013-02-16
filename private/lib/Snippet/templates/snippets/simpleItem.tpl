<div class='snp_item{if $inDialog} snp_dialog{/if}{if $data.__disabled__} snp_disabled{/if}'>
  {foreach from=$chunks item=chunk}
	<div class='snp_chunk'>
	  <table>
		{foreach from=$chunk key=field item=value}
		  {if !preg_match('#^__.+__$#', $field)}
			<tr>
			  <th>{if $field}{$field}{/if}</th>
			  <td><div>{$value}</div></td>
			</tr>
		  {/if}
		{/foreach}
	  </table>
	</div>
  {foreachelse}
	<div class='emptyFieldMsg'>Error: no se encontró información de este elemento</div>
  {/foreach}

  {if $chunks && $image}
	<img class="snp_item_img" src="{$image}" alt="{$data.__id__}" title="{$data.__id__}" />
  {/if}


  {if isset($snippet_extraHTML)}{$snippet_extraHTML}{/if}
</div>