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

  <span>Ingresar nuev{if $gender == 'm'}o{else}a{/if} {$name}</span>

  <div>
	<table>
	  {foreach from=$fields key=field item=atts}
		<tr>
		  <th>{$atts.name}</th>
		  <td>
			{if $atts.type == 'time'}
			  <input type='text' name='{$field}' value='{$NOW|date_locale:'i:s'}' />
			{elseif $atts.type == 'date'}
			  <input type='text' name='{$field}' value='{$NOW|date_locale:'Y-m-d'}' class='calendar' />
			{elseif $atts.type == 'datetime'}
			  <input type='text' name='{$field}' value='{$NOW|date_locale:'Y-m-d'}' class='calendar' />
			  <input type='text' name='{$field}' value='{$NOW|date_locale:'h:i'}' class='time_input' />
			{elseif $atts.type == 'area'}
			  <textarea name='{$field}'></textarea>
			{elseif $atts.type == 'list'}
			  <select name='{$field}'>
				{if $lists[$field].emptyField}
				  <option value=''>(seleccione un elemento)</option>
				{/if}
				{foreach from=$lists[$field].data key=key item=item}
				  <option value='{$key}'>{$item}</option>
				{/foreach}
			  </select>
			{elseif $atts.type == 'text'}
			  <input type='text' name='{$field}' value='' />
			{else}
			  <input type='text' name='{$field}' value='{$atts.type}' />
			{/if}
		  </td>
		</tr>
	  {/foreach}
	</table>
  </div>
  
</div>