<form id='frmEditEvent' action='javascript:void(0);'>

  <div style='float:left;'>
	{$required}			{* Required data input table *}
  </div>

  <div style='float:left;'>
	{$optional}			{* Optional data input table *}
  </div>
  
  <div class='FormTableBox' style='width:338px;'>
	Avisar <input type='text' name='reminder' value='{$reminder}' size='3' /> minutos antes a
	<br />
	<select multiple='multiple' name='remind[]' id='remind' style='width:180px;'>
	  {foreach from=$users key=k item=v}
		<option value='{$k}'{if isset($remindees[$k])} selected='selected'{/if}>{$v}</option>
	  {/foreach}
	</select>	
  </div>

  <div style='clear:both;'>
	<input type='button' class='button' id='btn_saveEvent' value='Guardar{if $id_event} Cambios{/if}' />
	<input type='hidden' id='id_event' name='id_event' value='{$id_event}' />
  </div>
  
</form>