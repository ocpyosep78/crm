{if not $data}
	<div class='innerListEmpty listPreLoad'>
		Sin resultados (ningún {$name|lower} cumple con los criterios de búsqueda aplicados)
	</div>
{else}
	<table class='innerList'>
	  {foreach from=$data key=id item=row}
		<tr bgcolor='{cycle values=$cycleValues}' class='innerListRow' for='{$id}'
		  {if $tipField && isset($row.$tipField)}title='{$row.$tipField}'{/if}>
		  {foreach from=$fields key=field item=atts}
			<td><div>{$row.$field|wordwrap:30:"<br />\n"|truncate:120:"...":true}</div></td>
		  {/foreach}
		  <td class='innerListTools'>
			<div>
			  {foreach from=$tools key=code item=tool}
				{if not $tool.disabled}
				  <img btn='{$code}' src='{$SNIPPET_IMAGES}/buttons/{$code}.png'
					alt='{$tool.name}' title='{$tool.name} {$name|lower}' />
				{/if}
			  {/foreach}
			</div>
		  </td>
		</tr>
	  {/foreach}
	</table>
{/if}