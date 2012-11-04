{if not $data}
	<div class='noResMsg'>No hay {$params.plural|lower} para listar.</div>
{else}
	<table class='data listTable'>
	  {foreach from=$data key=id item=row}
		<tr bgcolor='{cycle values=$cycleValues}' class='listRows highlight'
			{if $Permits->can($infoPage)}for='{$id}' {else}style='cursor:default;'{/if}title='{$row.tip}'>
		  {foreach from=$row key=key item=att}
			{if in_array($key, array_keys($fields))}<td><div>{$att}</div></td>{/if}
		  {/foreach}
		  <td>
			{foreach from=$tools key=axn item=permit}
			  {if $Permits->can($permit)}
				<div class='tblTools' for='{$id}' axn='{$axn}'>
				  <img src='app/images/buttons/{$axn}.png' alt='{$axns[$axn]}'
					title='{$axns[$axn]} {$params.name|lower}' />
				</div>
			  {/if}
			{/foreach}
		  </td>
		</tr>
	  {/foreach}
	</table>
{/if}