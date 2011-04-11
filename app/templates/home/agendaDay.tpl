<div class='agenda_dayWrapper' for='{$day.date|date_locale:'%Y-%m-%d'}'>
	<div class='agenda_dayName{if $day.isToday} agenda_today{/if}'>
		{$day.date|date_locale:"l":'':'date'}
	</div>
	<div class='agenda_dayDate'>{$day.date|date_locale:'d/m/y'}</div>
	<div class='agenda_dayContent'>
	  {foreach from=$day.events item=event}
		{include file='widgets/event.tpl' event=$event showDays=false}
	  {foreachelse}
		<div class='noResMsg'>No hay eventos registrados para este día</div>
	  {/foreach}
	</div>
</div>