<div id='agendaFilters'>
  <input type='hidden' id='thisDate' value='{$data[0].date|date_format:"Y-m-d"}' />

  {foreach from=$filters key=type item=filter}
	<div>
	  <select class='sel_agendaFilters' filter='{$type}'>
		{foreach from=$filter.options key=key item=val}
		  <option value='{$key}'{if $currFilters.$type == $key} selected='selected'{/if}>{$val}
		  </option>
		{/foreach}
	  </select>
	  Filtrar por {$filter.name}
	</div>
  {/foreach}

  <input type='checkbox' id='showRescheduled' value='1'{if $showRescheduled} checked='checked'{/if} />
  <label for="showRescheduled">Mostrar eventos cancelados / pospuestos</label>
</div>