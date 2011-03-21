{$comboList}

{* info table *}
<table class='infoTable'>
  {foreach from=$data key=field item=item}
	<tr>
	  <th>{$field}</th>
	  <td>{$item}</td>
	</tr>
  {/foreach}
</table>