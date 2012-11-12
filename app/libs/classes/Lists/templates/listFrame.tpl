{$comboList}

<div class='tableOutterWrapper'>
  <div class='tableTitles' id='tableTitles'>
	{foreach from=$fields key=key item=field}
	  {if $key && $key != 'id_estimate'}
		<div>{$field}</div>
	  {/if}
	{/foreach}
  </div>

  <div style='clear:both;'></div>

  <div class='tableOverflowWrapper'>
	<div class='tableWrapper' id='listWrapper'></div>
  </div>
</div>