<div class='snippet_item infoData'>

  <span>Detalle de {$name}{if $DEVELOPER_MODE} <strong>(objectID: {$objectID}){/if}</strong></span>

  {foreach from=$blocks item=block}
	  <div>
		<table>
		  {foreach from=$block key=field item=atts}
			<tr{if $atts.hidden || not $atts.name} style='display:none;'{/if}>
			  <th>{$atts.name}</th>
			  <td><div{if $editable && not $atts.frozen} class='viewItemEditable' for='{$field}'{/if}
				      {if $atts.frozen} class='snippetFrozenField'{/if}>{$data.$field}</div></td>
			</tr>
		  {/foreach}
		</table>
	  </div>
  {/foreach}
  
</div>