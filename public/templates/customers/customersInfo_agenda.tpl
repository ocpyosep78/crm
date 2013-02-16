<div class='tabEventsBox'>
  {foreach from=$events item=event}
	{include file='widgets/event.tpl' event=$event showDays=true}
  {foreachelse}
	<div class='noResMsg'>No hay eventos asociados a este cliente</div>
  {/foreach}
</div>