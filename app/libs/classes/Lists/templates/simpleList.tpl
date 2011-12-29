{*

	This template is not suppsed to be used directly by anyone but Lists object.
	
	A simple list is a list without search tools, combo list or pagination tools.
	
	It includes (if permissions are granted) tools to edit or add items in place, and tools to sort
	by column. Same as regular (tabular) lists, the configuration of each type of object to list is
	specified in ./staticData/{$code}.sd.php.
	
	Title width fixing is not needed as these lists are expected to be short hence requiring no
	overflow control. Simple lists are embedded in boxes with a loose height (auto).
	
*}

{literal}
<!-- IE wont' understand conditional comment without some commented output before...
	even if it's '&nbsp;' (amazing crap, isn't it?!) -->
<!--[if IE]>&nbsp;
	<style type='text/css'>
		.simpleList{ width:auto !important; }
	</style>
<![endif]-->
{/literal}


<table {if $simpleListID}id='{$simpleListID}' {/if}class='data simpleList' for='{$code}|{$modifier}'>

  {foreach from=$fields key=field item=name}											{* TITLES *}
	<th>{if is_array($name)}{$name[0]}{else}{$name}{/if}</th>
  {/foreach}
  <th>&nbsp;</th>
  
  {foreach from=$data key=id item=item}													{* ITEMS *}
	<tr for='{$id}' bgcolor='{cycle values=$cycleValues}' class='listRows'>
	  {foreach from=$fields key=field item=x}<td>{$item.$field}</td>{/foreach}			{* CELLS *}
	  <td class='simpleListTools'>
		{foreach from=$item.tools key=axn item=permit}									{* TOOLS *}
		  {if $Permits->can($permit)}
			<div class='tblTools' for='{$id}' axn='{$axn}'>
			  <img src='app/images/buttons/{$axn}.png' alt='{$axns[$axn]}'
				title='{$axns[$axn]} {$params.name|lower}' />
			</div>
		  {/if}
		{/foreach}
	  </td>
	</tr>
  {foreachelse}																			{* LIST IS EMPTY *}
	<tr>
	  <td colspan='{math equation="x + y" x=$fields|@count y=1}' class='noResMsg'>
		Esta lista aun no contiene elementos{if $canCreate} (utilice la línea debajo para crear nuevos elementos){/if}
	  </td>
	</tr>
  {/foreach}
  
  {if $canCreate}
	<tr class='addItemToSimpleList'>
	  {foreach from=$fields key=field item=name}										{* ADD ITEM *}
		<td>
		  {if is_array($name)}
			{assign var=code value=$name[1]}
			<select name='{$code}' id='{$simpleListID}{$code}'
			  {if $noInput.$code}disabled='disabled'{/if}>
			  {foreach from=$comboOptions[$code] key=k item=v}
				<option value='{$k}'{if $noInput.$code == $k} selected='selected'{/if}>{$v}</option>
			  {/foreach}
			</select>
		  {else}
			{assign var=code value=$field}
			<input type='text' name='{$code}' id='{$simpleListID}{$code}'
			  {if $noInput.$code !== null}
				value='{$noInput.$code}'
				def='{$noInput.$code}'
				disabled='disabled'
			  {/if} />
		  {/if}
		  <div class='tip' id='tip_{$simpleListID}{$field}'></div>
		</td>
	  {/foreach}
	  <td class='simpleListCreate'>
		<div class='tblTools' axn='create' id='SLcreateItem'>
		  <img src='app/images/buttons/add.png' alt='agregar' title='agregar' />
		</div>
		<span class='createItemText'>Agregar</span>
	  </td>
	</tr>
  {/if}
</table>