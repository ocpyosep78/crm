{if not $data}
	<div class='noResMsg'>No hay {$params.plural|lower} para listar.</div>
{else}
	<table id='listTable' class='data'>
	  {foreach from=$data key=id item=row}
		<tr bgcolor='{cycle values='#eaeaf5,#e5e5e5,#e5e5e5'}' class='listRows highlight'
			{if Access::can($infoPage)}for='{$id}' {else}style='cursor:default;'{/if}title='{$row.tip}'>
		  {foreach from=$row key=key item=att}
			{if in_array($key, array_keys($fields))}<td><div>{$att}</div></td>{/if}
		  {/foreach}
		  <td>
			{foreach from=$tools key=axn item=permit}
			  {if Access::can($permit)}
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