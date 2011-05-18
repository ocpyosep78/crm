<h2>Agenda</h2>

{foreach from=$events item=event}
  <div class='activity_item'>
	<div>
	  <strong>Fecha</strong>: <span class='link2model' for='agendaDay|{$event.ini|date_locale:'Y/m/d'}'>{$event.ini|date_locale:'Y-m-d'}</span> | <strong>Hora</strong>: {$event.ini|date_locale:'H:i'} (ingresado por <span class='link2model' for='usersInfo|{$event.creator}'>{$event.creator}</span>{if $event.target} para <span class='link2model' for='usersInfo|{$event.target}'>{$event.target}</span>{/if})
	  <div class='closeActivityEntry' for='{$event.activity_entry}'>[ marcar como resuelto ]</div>
	</div>
	{if $event.id_customer}
	  <div><strong>Cliente</strong>: <span class='link2model' for='customers|{$event.id_customer}'>{$event.customer}</span></div>
	{/if}
	<strong>Mensaje</strong>: {$event.event}
	<div class='linkLike' onclick="xajax_eventInfo('{$event.uid}');">Ver evento</div>
  </div>
{foreachelse}
	<div class='noResMsg'>No hay entradas pendientes en la agenda para este rubro.</div>
{/foreach}



<h2>Notas / Llamadas</h2>

{foreach from=$notes item=note}
  <div for='{$note.uid}' class='activity_item'>
	<div>
	  <strong>Fecha</strong>: {$note.date|date_locale:'Y-m-d'} | <strong>Hora</strong>: {$note.date|date_locale:'H:i'} (ingresado por <span class='link2model' for='usersInfo|{$note.by}'>{$note.by}</span>{if $note.user} para <span class='link2model' for='usersInfo|{$note.user}'>{$note.user}</span>{/if})
	  <div class='closeActivityEntry' for='{$note.activity_entry}'>[ marcar como resuelto ]</div>
	</div>
	{if $note.id_customer}
	  <div><strong>Cliente</strong>: <span class='link2model' for='customers|{$note.id_customer}'>{$note.customer}</span></div>
	{/if}
	<strong>Mensaje</strong>: {$note.note}
  </div>
{foreachelse}
	<div class='noResMsg'>No hay notas ni llamadas pendientes en este rubro.</div>
{/foreach}

{* Array(
	[0] => Array(
		[id] => 3
		[model] => notes
		[uid] => 1
		[id_note] => 1
		[type] => technical
		[by] => ibrazuna
		[note] => Se llamo, y Lilian no se encontraba, llamar nuevamente
		[date] => 2011-05-18 13:17:12
		[user] => 
		[id_customer] => 198
	)
) *}