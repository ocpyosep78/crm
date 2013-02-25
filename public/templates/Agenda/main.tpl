{include file='widgets/agenda_filters.tpl'}

{if Access::can('createEvent')}
	<input type='button' class='button' id='btn_createEvent' value='Nuevo Evento' />
{/if}

<div id='agenda_move'>
  <img title='semana anterior' src='{$IMAGES_URL}/buttons/go-left.gif' alt='' for='{$prev}' />
  <input type='text' id='agenda_calendar' class='calendar' style='display:none;' value='{$data[0].date|date_format:'Y/m/d'}' />
  <img title='semana siguiente' src='{$IMAGES_URL}/buttons/go-right.gif' alt='' for='{$next}' />
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