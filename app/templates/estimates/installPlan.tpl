<form id='frm_plan' name='plan' action='javascript:void(0);'>

	<input type='hidden' name='id_estimate' value='{$id_estimate}' />
	
	<div class='installPlanRow'>
		Cantidad<br />
		<input type='text' name='amount' value='' size='5' />
	</div>
	
	<div class='installPlanRow'>
		Producto<br />
		<select name='id_product'>
		  {foreach from=$products key=k item=item}
			<option value='{$k}'{if $product == $k} selected='selected'{/if}>{$item.name}</option>
		  {/foreach}
		</select>
	</div>
	
	<div class='CLEAR'>
		Descripción (ubicación y otros detalles)<br />
		<input type='text' name='position' value='' size='80' />
	</div>
	
	<div>
		<input type='submit' class='button' value='Agregar' />
		<input type='button' class='button' value='Volver' id='backToEstimateInfo' />
	</div>
	
</form>

	<table id='installPlan' border='0' cellpadding='3' cellspacing='0'>
	  
	  <tr>
		<th colspan='2'>Cantidad</th>
		<th>Producto</th>
		<th>Ubicación y otros detalles</th>
	  </tr>
	  
	  {foreach from=$data item=row}
		<tr class='highlight'>
		  <td title='quitar de la lista' for='{$row.id_plan}'><img src='app/images/buttons/delete.png' /></td>
		  <td class='planAmount'>{$row.amount}</td>
		  <td class='planName'>{$row.name}</td>
		  <td class='planPosition'>{$row.position}</td>
		</tr>
	  {/foreach}
	  
	</table>