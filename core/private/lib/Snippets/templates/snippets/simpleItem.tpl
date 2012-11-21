<div class='snp_item'>
  {if !$inDialog}<div>{$title}</div>{/if}

  {if $disabled}<h3>ELIMINADO</h3>{/if}

  {foreach from=$chunks item=chunk}
	<div class='snp_chunk'>
	  <table>
		{foreach from=$chunk key=field item=value}
		  {if $field != '__disabled__'}
			<tr>
			  <th>{$field}</th>
			  <td><div>{$value}</div></td>
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