<form id='frmEditEvent' action='javascript:void(0);'>

  <div style='float:left;'>
	{$required}			{* Required data input table *}
  </div>

  <div style='float:left;'>
	{$optional}			{* Optional data input table *}
  </div>

  <div style='clear:both;'>
	<input type='button' class='button' id='btn_saveEvent' value='Guardar{if $id_event} Cambios{/if}' />
	<input type='hidden' id='id_event' name='id_event' value='{$id_event}' />
  </div>
  
</form>