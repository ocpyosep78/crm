<div class='infoData'>

  <span>Detalle de {$name}{if $DEVELOPER_MODE} <strong>(objectID: {$params}){/if}</strong></span>

  {foreach from=$blocks item=block}
	  <div>
		<table>
		  {foreach from=$block key=field item=atts}
			<tr{if $atts.hidden || not $atts.name} style='display:none;'{/if}>
			  <th>{$atts.name}</th>
			  <td><div>{$data.$field}</div></td>
			</tr>
		  {/foreach}
		</table>
	  </div>
  {/foreach}
  
</div>