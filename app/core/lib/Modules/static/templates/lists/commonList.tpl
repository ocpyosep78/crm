<div class='listSearch'>
	<img class='CloseButtonHover' src="app/images/buttons/closeHover.png" alt='cerrar' title='cerrar ventana' />
	<img class='CloseButton' src="app/images/buttons/close.png" alt='cerrar ventana' />
	<div>Filtrar por <span></span></div>
	<input type='text' size='30' value='' />
</div>

<div class='commonListWrapper'>

  <div class='listTitles'>
	{foreach from=$fields item=field}
	  {if $field && $fieldsCfg.$field.name && not $fieldsCfg.$field.hidden}
		{assign var=fieldName value=$fieldsCfg.$field.name}
		<div for='{$field}'>{$fieldName}</div>
	  {/if}
	{/foreach}
  </div>
  
  <div style='clear:both;'></div>
  
  <div class='commonListInnerWrapper'>
	<div class='listWrapper'></div>
  </div>
  
</div>