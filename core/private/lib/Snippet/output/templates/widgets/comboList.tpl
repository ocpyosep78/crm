<div class='comboListBox'>
	<select class='comboList'>
	  <option value='' {if not $combo.selected} selected='selected'{/if}>
		(seleccionar {if $gender == 'f'}una{else}un{/if} {$name|lower} de esta lista para ver información detallada)
	  </option>
	  {foreach from=$combo.list key=k item=v}
		<option value='{$k}'{if $k == $combo.selected} selected='selected'{/if}>{$v}</option>
	  {/foreach}
	</select>
</div>