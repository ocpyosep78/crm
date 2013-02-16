{if not $data}
	<div class='innerListEmpty listPreLoad'>
		Sin resultados (ningún {$name|lower} cumple con los criterios de búsqueda aplicados)
	</div>
{else}
	<table class='innerList'>
	  {foreach from=$data item=row}
		<tr bgcolor='{cycle values=$cycleValues}' class='innerListRow highlight{if $row.__disabled__} snp_disabled{/if}' for='{$row.__id__}' title='{$row.__description__}'>
		  {foreach from=$row key=field item=value}
			{if !preg_match('#^__.+__$#', $field)}
			  <td><div>{$value|wordwrap:30:"<br />\n"|truncate:120:"...":true}</div></td>
			{/if}
		  {/foreach}
		  <td class='innerListTools'>
			<div>
			  {foreach from=$buttons key=action item=btn}
				{if (!$btn.depends || !empty($row[$btn.depends])) && (!$btn.avoids || empty($row[$btn.avoids]))}
				  <img src='{$btn.icon}' alt='{$action}' title='{$btn.title}' />
				{/if}
			  {/foreach}
			</div>
		  </td>
		</tr>
	  {/foreach}
	</table>
{/if}