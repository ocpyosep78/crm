{* Receives an array as input, named event, with following keys:
	- id_event
	- type
	- ini
	- customer
	- target
	- event *}

<p class='eventUnit event{$event.type}{if $event.closed} eventClosed{/if}{if $event.rescheduled} eventRescheduled{/if}' onmouseover="highLight(this, '#f0f0f0');" {if $event.closed}title='{$event.closed}'{else}title='click para ver información detallada del evento'{/if}>

	<input type='hidden' name='id_event' value='{$event.id_event}' />
	
	<img class='agenda_eventUser' alt='' title='{$event.creator}' src='app/images/users/{$event.creator}.png' />
	<img class='agenda_eventType' alt='' title='{$types[$event.type]}'
		src='app/images/agendaEvents/{if $event.rescheduled}rescheduled/{/if}{$event.type}.png' />
	
	<span class='agenda_eventInfo'>
		{if $showDays}{$event.ini|date_locale:'d/m/Y, H:i'}
		{else}{$event.ini|date_locale:'H:i'}{/if}
		{if $event.end}/ {$event.end|date_locale:'H:i'}{/if}
	</span>
	
	{if $event.customer}<span class='agenda_customer'>[ {$event.customer} ]</span>{/if}
	{if $event.target} <span class='agenda_targetUser'>&gt; {$event.target}</span>{/if}
	
	<span class='agenda_eventText'>- {$event.event}</span>
	
</p>