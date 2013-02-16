<div style='float:right;'>{$comboList}</div>

<div id='listBox'><div id='estimateListWrapper'><div id="suggestList"></div></div></div>

{include file='estimates/_common/header.tpl' miniHeader=false}

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
	   <tr class='quoteLine noProduct quoteBasicLine'>
		<td>
			<input class='quoteAmount quoteInput' type='text' value='' size='6' maxlength="4" />
		</td>
		<td>
		  <div class='suggestBox'>
			<div class='suggestDIV'></div>
			<input class='productName' type='text' value='' size='40' />
		  </div>
		</td>
		<td>
			<input class='quotePrice quoteInput' type='text' value='' size='10' maxlength="10" />
		</td>
		<td class='autoCell quoteSub'></td>
		<td class='autoCell quoteTax'></td>
		<td class='autoCell quoteTotal'></td>
	   </tr>
	</tbody>
	<tfoot>
	  <tr>
		<th></th>
		<th colspan='2' align="center">Totales</th>
		<th id='tSubTotal'>0.00</th>
		<th id='tTax'>0.00</th>
		<th id='tTotal'>0.00</th>
	  </tr>
	</tfoot>
  </table>
</div>