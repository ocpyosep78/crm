{* Receives an array as input, named event, with following keys:
	- id_event
	- type
	- ini
	- customer
	- target
	- event *}

<p class='eventUnit event{$event.type}{if $event.closed} eventClosed{/if}{if $event.rescheduled} eventRescheduled{/if}' {if $event.closed}title='Cerrado/Cancelado: {$event.closed}'{else}title='ver detalle'{/if}>
	<input type='hidden' name='id_event' value='{$event.id_event}' />

	<img class='agenda_eventUser' alt='' title='{$event.creator}' src='{$event.creatorimg}' />
	<img class='agenda_eventType' alt='' title='{$types[$event.type]}'
		src='{$IMAGES_URL}/agendaEvents/{if $event.rescheduled}rescheduled/{/if}{$event.type}.png' />

	<span class='agenda_eventInfo'>
		{if $showDays}{$event.ini|date_format:'d/m/Y, H:i'}
		{else}{$event.ini|date_format:'H:i'}{/if}
		{if $event.end}/ {$event.end|date_format:'H:i'}{/if}
	</span>

	{if $event.customer}<span class='agenda_customer'>[ {$event.customer} ]</span>{/if}
	{if $event.target} <span class='agenda_targetUser'>&gt; {$event.target}</span>{/if}

	<span class='agenda_eventText'>- {$event.event}</span>

	<span class='eventTools'>
	  {if Access::can('editEvent') && $event.canEdit && !$event.closed}
		<img for='edit'   src='{$IMAGES_URL}/agendaTools/edit.png' title='editar' />
		<img for='cancel' src='{$IMAGES_URL}/agendaTools/cancel.png' title='cancelar/posponer' />
		<img for='close'  src='{$IMAGES_URL}/agendaTools/close.png' title='cerrar' />
	  {/if}
	</span>
</p>