<div style='float:right;'>{$comboList}</div>

{include file='estimates/_common/header.tpl' estimate=$data}

<div id='quotesTableWrapper'>
  <table class='quotesTable' cellpadding='0' cellspacing='0' border='0'>
	<thead>
	  <tr>
		<th>Cantidad</th>
		<th>Artículo</th>
		<th>Precio Unit.</th>
		<th width='45px'>Importe</th>
		<th width='45px'>IVA</th>
		<th width='45px'>Total</th>
	  </tr>
	</thead>
	<tbody>
	  {foreach from=$data.detail item=row}
		<tr class='quoteLine'>
		  <td class='autoCell'>{$row.amount}</td>
		  <td class='estimateInfoName'>{$row.name}</td>
		  <td class='autoCell'>{$row.price}</td>
		  <td class='autoCell'>{$row.subTotal|number_format:2}</td>
		  <td class='autoCell'>{$row.tax|number_format:2}</td>
		  <td class='autoCell'>{$row.total|number_format:2}</td>
		</tr>
	  {/foreach}
	</tbody>
	<tfoot>
	  <tr>
		<th></th>
		<th colspan='2' align="center">Totales</th>
		<th id='tSubTotal'>{$totals.subTotal|number_format:2}</th>
		<th id='tTax'>{$totals.tax|number_format:2}</th>
		<th id='tTotal'>{$totals.total|number_format:2}</th>
	  </tr>
	</tfoot>
  </table>
</div>

<div id='tmpDivToPrint'></div>