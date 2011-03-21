{*
	Receives the following data:
		$combo = array(
			code					=> list's code (i.e. customers, users, etc.),
			params					=> array(name => screenName for this combo),
			list					=> hash of options: value => text,
			selected				=> selected element by option value,
		)
*}



{if $combo && $combo.list}

	<div class='comboListBox'>
		{if $combo.params.name}Ver Información detallada de {$combo.params.name}{else}Información detallada{/if}
		<select class='comboList' for='{$combo.code}'>
		  <option value=''{if not $combo.selected} selected='selected'{/if}>(seleccionar)</option>
		  {foreach from=$combo.list key=k item=v}
			<option value='{$k}'{if $k == $combo.selected} selected='selected'{/if}>{$v}</option>
		  {/foreach}
		</select>
	</div>
	
{/if}