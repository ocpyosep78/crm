<div id='TableSearchBox' class='TableSearchBoxes'>
	<img class='CloseButtonHover' src="app/images/buttons/closeHover.png" alt='cerrar' title='cerrar ventana' />
	<img id='TableSearchCloseButton' class='CloseButton' src="app/images/buttons/close.png" alt='cerrar' />
	<div>Filtrar por <span></span></div>
	<input type='text' size='30' id='TableSearchInput' />
</div>


{$comboList}


<div class='tableOutterWrapper'>

  <div class='tableTitles' id='tableTitles'>
	{foreach from=$fields key=key item=field}
	  {if $key && $key != 'id_estimate'}
		<div>
		  {$field}
		  <img class='tableColumnSearch' src='app/images/buttons/search.gif'
			title='filtrar por campo {$field|lower}' for='{$key}' alt='{$field|lower}' />
		</div>
	  {/if}
	{/foreach}
  </div>
  
  <div style='clear:both;'></div>
  
  <div class='tableOverflowWrapper'>
	<div class='tableWrapper' id='listWrapper'></div>
  </div>
  
</div>