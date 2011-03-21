<table id='estimateInfoTbl' border='0'>
  <tr>
  {if not $miniHeader}			{* Mini-header is meant for small presentations (i.e. for printing) *}
	<td rowspan="6" style='padding-right:10px;'>
	  <img id='img_system' src='app/images/systems/{$system}.png' alt='img' height='150px' />
	</td>
  {/if}
	<th>{$estimateType}</th>
	<td>{if $edit}<input type='text' id='param_estimate' value='{$estimate.estimate}' />
		{else}{if $data.estimate}{$data.estimate}{else}(sin especificar){/if}{/if}</td>
  </tr>
  <tr>
	<th>Número de Orden</th>
	<td>{if $edit}<input type='text' id='param_orderNumber' value='{$estimate.orderNumber}' />
		{else}{if $data.orderNumber}{$data.orderNumber}{else}(sin especificar){/if}{/if}</td>
  </tr>
  <tr>
	<th>Cliente</th>
	{if $edit}
	  <td>
		<select id='param_id_customer'>
		  <option value=''>(seleccionar)</option>
		  {foreach from=$data.customersList key=k item=v}<option value='{$k}'
			{if $k == $estimate.id_customer} selected='selected'{/if}>{$v}</option>{/foreach}
		</select>
	  </td>
	{else}
	  {if $data.id_customer}
		<td title='ver datos del cliente'><a href='javascript:void(0);' target='_blank'
		  onclick="getPage(event, 'customersInfo', ['{$data.id_customer}']);">{$data.customer}</a></td>
	  {else}<td>(sin especificar)</td>{/if}
	{/if}
	</td>
  </tr>
  <tr>
	<th>Sistema</th>
	{if $edit}
	  <td>
		<select id='param_id_system'>
		  <option value=''>(seleccionar)</option>
		  {foreach from=$data.systems key=k item=v}<option value='{$k}'
			{if $k == $estimate.id_system} selected='selected'{/if}>{$v}</option>{/foreach}
		</select>
	  </td>
	{else}
	  <td>{if $data.system}{$data.system}{else}(sin especificar){/if}</td>
	{/if}
  </tr>
 {if not $miniHeader}
  <tr><td>&nbsp;</td></tr>
  <tr>
	<td colspan="2">
	  <input type='hidden' id='hdn_id_estimate' value='{$estimate.id_estimate}' />
	  {if $edit}<input type='button' value='Guardar' id='btn_save' />
	  {else}
		{if $Permits->can('editEstimates')}
		  <input type='button' value='Editar' id='btn_edit' />
		{/if}
	  {/if}
	  {if not $edit}
		<input type='button' value='Imprimir' id='btn_print' />
		{if $Permits->can('installPlan')}
		  <input type='button' value='Plan de Obras' id='btn_design' />
		{/if}
		{if $Permits->can('estimatePDF')}
		  <input type='button' value='Exportar (PDF)' id='btn_exportPDF' />
		{/if}
	  {/if}
	</td>
  </tr>
 {/if}
</table>