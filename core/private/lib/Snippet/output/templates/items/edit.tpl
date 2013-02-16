{*

	Accepted types of fields:
		
		- text (default)
		- image
		- list (combo)
		- date (calendar)
		- time
		- datetime (time + calendar)
		- option (radio)
		- options (checkboxes)

*}

<div class='snippet_item createForm'>

  {if $data}
	<span>Editar {$name}</span>
  {else}
	<span>Ingresar nuev{if $gender == 'm'}o{else}a{/if} {$name}</span>
  {/if}

  <div>
   <form class='snippet_createForm' action='javascript:void(0);'>
	{if $objectID}<input type='hidden' name='__objectID__' value='{$objectID}' />{/if}
	<table class='snippet_createTable'>
	  {foreach from=$fields key=field item=atts}
		<tr>
		  <th>{$atts.name}</th>
		  <td>
			{if $atts.type == 'time'}
			  <input type='text' name='{$field}'
				value='{if $data}{$data[$field]}{else}{$NOW|date_locale:'i:s'}{/if}' />
			{elseif $atts.type == 'date'}
			  <input type='text' name='{$field}' class='calendar'
				value='{if $data}{$data[$field]}{else}{$NOW|date_locale:'Y-m-d'}{/if}' />
			{elseif $atts.type == 'datetime'}
			  <input type='text' name='{$field}' class='calendar'
				value='{if $data}{$data[$field]|date_locale:'Y-m-d'}{else}{$NOW|date_locale:'Y-m-d'}{/if}' />
			  <input type='text' name='{$field}' class='time_input'
				value='{if $data}{$data[$field]|date_locale:'H:i'}{else}{$NOW|date_locale:'H:i'}{/if}' />
			{elseif $atts.type == 'area'}
			  <textarea name='{$field}'>{if $data}{$data[$field]}{/if}</textarea>
			{elseif $atts.type == 'list'}
			  <select name='{$field}'>
				{if $lists[$field].emptyField}
				  <option value=''>(seleccione un elemento)</option>
				{/if}
				{foreach from=$lists[$field].data key=key item=item}
				  <option value='{$key}'
					{if $data && $data[$field] == $key}selected='selected'{/if}>{$item}</option>
				{/foreach}
			  </select>
			{elseif $atts.type == 'image'}
			  <input type='file' name='{$field}' value='{if $data}{$data[$field]}{/if}' />
			{elseif $atts.type != 'text'}
			  <input type='text' name='{$field}' value='{$atts.type} not implemented{if $data}: {$data[$field]}{/if}' />
			{else}
			  <input type='text' name='{$field}' value='{if $data}{$data[$field]}{/if}' />
			{/if}
		  </td>
		</tr>
	  {/foreach}
	  <tr class='snippet_submitRow'>
		<td colspan='2' align='center'>
		  <input type='button' class='button' value='Guardar' />
		</td>
	  </tr>
	</table>
   </form>
  </div>
  
</div>