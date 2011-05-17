{$info}		{* Snippet 'view' *}

<div class='estimates_pack_detail'>
  <table cellpadding='0' cellspacing='0'>
	<tr>
	  <th class='fields'>&nbsp;</th>
	  {foreach from=$data key=k item=v}
		<th><span onclick="getPage('estimatesInfo', ['{$k}']);">{$v.name}</span></th>
	  {/foreach}
	</tr>
	<tr>
	  <th class='fields'>Costo</th>
	  {foreach from=$data item=v}<td>${$v.cost}</td>{/foreach}
	</tr>
	<tr>
	  <th class='fields'>Presupuestado</th>
	  {foreach from=$data item=v}<td>${$v.price}</td>{/foreach}
	</tr>
	<tr>
	  <th class='fields'>Utilidades</th>
	  {foreach from=$data item=v}
		<td{if $v.utility < 0} class='redNumbers'{/if}>{$v.utility}%</td>
	  {/foreach}
	</tr>
  </table>
</div>