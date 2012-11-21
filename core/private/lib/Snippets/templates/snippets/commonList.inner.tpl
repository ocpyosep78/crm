{if not $data}
	<div class='innerListEmpty listPreLoad'>
		Sin resultados (ningún {$name|lower} cumple con los criterios de búsqueda aplicados)
	</div>
{else}
	<table class='innerList'>
	  {foreach from=$data item=row}
		<tr bgcolor='{cycle values=$cycleValues}' class='innerListRow highlight{if !empty($row.__disabled__)} snp_item_removed{/if}' for='{$row.$primary}'{if isset($row.$toolTip)} title='{$row.$toolTip}'{/if}>
		  {foreach from=$row key=field item=value}
			{if ($field != $primary) && ($field != '__disabled__')}
			  <td><div>{$value|wordwrap:30:"<br />\n"|truncate:120:"...":true}</div></td>
			{/if}
		  {/foreach}
		  <td class='innerListTools'>
			<div>
			  {foreach from=$listButtons key=code item=button}
				<img src='{$SNP_IMAGES}/buttons/{$code}.png' alt='{$code}' title='{$button} {$name|lower}' />
			  {/foreach}
			</div>
		  </td>
		</tr>
	  {/foreach}
	</table>
{/if}