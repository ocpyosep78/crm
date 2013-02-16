{assign var=Lists value=$Builder->get('Lists')}

<form name='frmOldSales' action='javascript:void(0);'>

	<h3 id='registerSaleTitle'>Registrar Venta</h3>
	
	<div id='saleTypeOptions'>
		<div><input type='radio' name='saleType' value='system' /> Sistema</div>
		<div><input type='radio' name='saleType' value='product' /> Productos</div>
		<div><input type='radio' name='saleType' value='service' /> Servicios</div>
	</div>

	<table id='tblNewSales'>
	
	  <tr>
		<th valign="top">Cliente</th>
		<td>
		  <select name='id_customer'>
			<option value=''>(seleccionar)</option>
			{foreach from=$Lists->customers() key=k item=v}
			  <option value='{$k}'>{$v}</option>
			{/foreach}
		  </select>
		</td>
	  </tr>
	  
	  <tr>
		<th valign="top">Vendedor</th>
		<td>
		  <select id='seller' disabled='disabled'>
			<option value=''>(automático)</option>
			{foreach from=$Lists->sellers() key=k item=v}
			  <option value='{$k}'>{$v}</option>
			{/foreach}
		  </select>
		</td>
	  </tr>
	  
	  <tr>
		<th valign="top">Factura</th>
		<td><input type='text' name='invoice' value='' /></td>
	  </tr>
	  
	  <tr>
		<th valign="top">Fecha</th>
		<td><input type='text' class='calendar' name='date' value='{$tmpDate}' /></td>		{* TEMP *}
	  </tr>
	  
	  <tr>
		<th valign="top">Garantía</th>
		<td>
		  <select name='warranty'>
			{foreach from=$Lists->warranties() key=k item=v}
			  <option value='{$k}'>{$v}</option>
			{/foreach}
		  </select>
		</td>
	  </tr>
	  
	  <tr>
		<th valign="top">Sistema</th>
		<td>
		  <select name='id_system'>
			<option value=''>(seleccionar)</option>
			{foreach from=$Lists->systems() key=k item=v}
			  <option value='{$k}'>{$v}</option>
			{/foreach}
		  </select>
		</td>
	  </tr>
	  
	  <tr>
		<th valign="top">Instalador</th>
		<td>
		  <select name='id_installer'>
			<option value=''>(seleccionar)</option>
			{foreach from=$Lists->installers() key=k item=v}
			  <option value='{$k}'>{$v}</option>
			{/foreach}
		  </select>
		</td>
	  </tr>
	  
	  <tr>
		<th valign="top">Técnico / Responsable</th>
		<td>
		  <select name='technician'>
			<option value=''>(seleccionar)</option>
			{foreach from=$Lists->users() key=k item=v}
			  <option value='{$k}'>{$v}</option>
			{/foreach}
		  </select>
		</td>
	  </tr>
	  
	  <tr>
		<th valign="top">Descripción Breve</th>
		<td>
		  <input type='text' name='description' value='' size='60' />
		</td>
	  </tr>
	  
	  <tr>
		<td colspan='2'>
		  <input type='submit' class='button' value='Registrar Venta' />
		</td>
	  </tr>
	  
	</table>

</form>