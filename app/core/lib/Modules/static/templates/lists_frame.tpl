<div id='TableSearchBox' class='TableSearchBoxes'>
	<img class='CloseButtonHover' src="app/images/buttons/closeHover.png" alt='cerrar' title='cerrar ventana' />
	<img id='TableSearchCloseButton' class='CloseButton' src="app/images/buttons/close.png" alt='cerrar' />
	<div>Filtrar por <span></span></div>
	<input type='text' size='30' id='TableSearchInput' />
</div>

<div class='tableOutterWrapper'>

  <div class='tableTitles' id='tableTitles'>
	{foreach from=$fields item=field}
	  {if $field && $fieldsCfg.$field.name && not $fieldsCfg.$field.hidden}
		{assign var=fieldName value=$fieldsCfg.$field.name}
		<div>
		  {$fieldName}
		  <img class='tableColumnSearch' src='app/images/buttons/search.gif' for='{$field}'
			title='filtrar por campo {$fieldName|lower}' alt='{$fieldName|lower}' />
		</div>
	  {/if}
	{/foreach}
  </div>
  
  <div style='clear:both;'></div>
  
  <div class='tableOverflowWrapper'>
	<div class='tableWrapper' id='listWrapper'></div>
  </div>
  
</div>