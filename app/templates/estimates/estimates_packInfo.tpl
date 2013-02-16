{$info}		{* Snippet 'view' *}

<div class='estimates_pack_tools' id='addEstimate'>
	Agregar Presupuesto a la Lista
	<select id='estimates_pack_tools_add' for='{$id}'>
	  <option value=''>(seleccionar)</option>
	  {foreach from=$left key=k item=v}<option value='{$k}'>{$v}</option>{/foreach}
	</select>
</div>

<div class='estimates_pack_tools' id='createEstimate'>
	Crear nuevo presupuesto para esta lista
</div>

{if $data}
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
{/if}