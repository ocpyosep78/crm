<div class='comboListBox'>
	{if $name}Ver Informaci�n detallada de {$name}{else}Informaci�n detallada{/if}
	<select class='comboList'>
	  <option value=''{if not $combo.selected} selected='selected'{/if}>(seleccionar)</option>
	  {foreach from=$combo.list key=k item=v}
		<option value='{$k}'{if $k == $combo.selected} selected='selected'{/if}>{$v}</option>
	  {/foreach}
	</select>
</div>