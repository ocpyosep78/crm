<div id='agendaFilters'>
  <input type='hidden' id='thisDate' value='{$data[0].date|date_format:"%Y-%m-%d"}' />
  <input type='checkbox' id='showRescheduled' value='1'{if $showRescheduled} checked='checked'{/if} />
  	Mostrar eventos cancelados / pospuestos
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
</div>

{if $Permits->can('createEvent')}
	<input type='button' class='button' id='btn_createEvent' value='Nuevo Evento' />
{/if}
  
<div id='agenda_move'>
  <img title='semana anterior' src='app/images/buttons/go-left.gif' alt='' for='{$prev}' />
  <input type='text' id='epochTrigger' class='calendar' />
  <img title='semana siguiente' src='app/images/buttons/go-right.gif' alt='' for='{$next}' />
</div>

<table id='agendaTbl' cellspacing="7">
	<tr>
		<td>{include file='home/agendaDay.tpl' day=$data[0]}</td>
		<td>{include file='home/agendaDay.tpl' day=$data[1]}</td>
		<td>{include file='home/agendaDay.tpl' day=$data[2]}</td>
	</tr>
	<tr>
		<td>{include file='home/agendaDay.tpl' day=$data[3]}</td>
		<td>{include file='home/agendaDay.tpl' day=$data[4]}</td>
		<td class='agendaHalfDays'>
			{include file='home/agendaDay.tpl' day=$data[5]}
			{include file='home/agendaDay.tpl' day=$data[6]}
		</td>
	</tr>
</table>