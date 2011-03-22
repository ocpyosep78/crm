<div class='infoData'>

  <span>Detalle de {$name}</span>

  {foreach from=$blocks item=block}
	<div>
	  <div>
		<table>
		  {foreach from=$block item=field}
			{if isset($fieldsCfg.$field)}
			  <tr{if $fieldsCfg.$field.hidden || not $fieldsCfg.$field.name} style='display:none;'{/if}>
				<th>{$fieldsCfg.$field.name}</th>
				<td><div>{$data.$field}</div></td>
			  </tr>
			{/if}
		  {/foreach}
		</table>
	  </div>
	</div>
  {/foreach}
  
</div>