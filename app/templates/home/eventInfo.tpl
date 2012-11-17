{if $event.closed}
  <div class='eventClosedMsg'>
	{if $event.rescheduled}<p class='eventInfoRescheduled'>Reagendado o Cancelado</p>{/if}
  	<p><strong>Cerrado</strong> por {$event.closedBy} ({$event.closedOn|date_locale:'d/m/Y, H:i'})</p>
	<p><i>{$event.closed}</i></p>
  </div>
{/if}

<div class='eventInfoTitle'>{$event.ini|date_locale:'l, d \d\e F \d\e Y'}</div>
<div class='eventInfoSubTitle'>{$event.ini|date_locale:'H:i'}{if $event.end} - {$event.end|date_locale:'H:i'}{/if}</div>

<img class='eventInfo_Creator' alt='' title='{$event.creator}' src='{$event.creatorimg}' />

<table class='eventInfoTbl' cellpadding="3" cellspacing="0">
	<tr>
		<th>Descripción</th>
		<td style='font-style:italic;'>{$event.event}</td>
	</tr>
	<tr>
		<th>Agendado para</th>
		<td>
			{$event.ini|date_locale:'d/m/Y, H:i'}
			{if $event.end} - {$event.end|date_locale:'H:i'}{/if}
		</td>
	<tr>
		<th>Vinculado a Cliente</th>
		{if $event.id_customer}
		  <td class='link2model' onclick="getPage('customersInfo', ['{$event.id_customer}']);">{$event.customer}</td>
		{else}<td class='agenda_noInfo'>(ninguno)</td>{/if}
	</tr>
	<tr>
		<th>Asignado a Usuario</th>
		{if $event.target}
		  <td class='link2model' onclick="getPage('usersInfo', ['{$event.user}']);">{$event.target}</td>
		{else}<td class='agenda_noInfo'>(ninguno)</td>{/if}
	</tr>
	<tr>
		<th>Tipo</th>
		{if $event.type}
		  <td>{$event.type}</td>
		{else}<td class='agenda_noInfo'>(sin especificar)</td>{/if}
	</tr>
	<tr>
		<th>Creado por</th>
		<td>
			<span>{$event.creator}</span>
			({$event.created|date_locale:'d/m/Y, H:i'})
		</td>
	</tr>
	{if $editions}
	  <tr>
		<th>Editado por:</th>
		<td>
		  {foreach from=$editions item=edition}
			<span>{$edition.by}</span>
			({$edition.on|date_locale:'d/m/Y, H:i'})<br />
		  {/foreach}
		</td>
	  </tr>
	{/if}
</table>

{if $Permits->can('editEvent') && $event.canEdit}
  <div class='editEventFromEventInfo'>
   {if not $event.closed}
	<input type='button' class='button' value='Editar Evento'
		onclick="getPage(event, 'editEvent', ['{$event.id_event}']);" />
	<input type='button' class='button' value='Cerrar Evento'
		onclick="closeAgendaEvent('{$event.id_event}');" />
	<input type='checkbox' id='rescheduled' value='1' /> Cancelado / Pospuesto
   {/if}
  </div>
{/if}