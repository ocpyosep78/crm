<div class='snp_comboListBox'>
	<select class='comboList'>
	  <option value='' {if not $selected} selected='selected'{/if}>
		(seleccionar {if $gender == 'f'}una{else}un{/if} {$name|lower} para abrir detalle)
	  </option>
	  {foreach from=$list key=k item=v}
		<option value="{$k}" title="{$v}"{if $k == $selected} selected="selected"{/if}>{$v|truncate:60:'...'}</option>
	  {/foreach}
	</select>
</div>