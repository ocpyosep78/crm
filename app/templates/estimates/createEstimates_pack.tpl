<table>
  <tr>
	<th>Nombre</th>
	<td><input type='text' id='createEstimatesPack_name' value='' /></td>
  </tr>
  <tr>
	<th>Cliente</th>
	<td>
	  <select id='createEstimatesPack_id_customer'>
		<option value=''>(seleccionar)</option>
		{foreach from=$customers key=k item=v}
		  <option value='{$k}'>{$v}</option>
		{/foreach}
	  </select>
	</td>
  </tr>
  <tr>
	<td colspan='2'>
	  <input type='button' id='createEstimatesPack' value='Crear y Comenzar a Editar' />
	</td>
  </tr>
</table>