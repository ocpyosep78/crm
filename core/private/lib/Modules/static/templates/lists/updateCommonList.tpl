{if not $data}
	<div class='commonListEmpty'>
		Sin resultados (ningún {$name|lower} cumple con los criterios de búsqueda aplicados)
	</div>
{else}
	<table class='data'>
	  {foreach from=$data key=id item=row}
		<tr bgcolor='{cycle values=$cycleValues}' class='listRows' for='{$id}'
		  {if $tipField && isset($row.$tipField)}title='{$row.$tipField}'{/if}>
		  {foreach from=$fields item=field}
			<td><div>{$row.$field|wordwrap:35:"<br />\n"}</div></td>
		  {/foreach}
		  <td>{* Tools here *}</td>
		</tr>
	  {/foreach}
	</table>
{/if}









{*			{foreach from=$tools key=axn item=permit}
			  {if $Permits->can($permit)}
				<div class='tblTools' for='{$id}' axn='{$axn}'>
				  <img src='app/images/buttons/{$axn}.png' alt='{$axns[$axn]}'
					title='{$axns[$axn]} {$params.name|lower}' />
				</div>
			  {/if}
			{/foreach} *}