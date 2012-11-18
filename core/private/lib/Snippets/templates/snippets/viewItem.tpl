<div class='snippet_item infoData'>
  <span>Detalle de {$name}{if $DEVMODE} <strong>(objectID: {$objectID}){/if}</strong></span>
    {foreach from=$chunks item=chunk}
      <div>
        <table>
          {foreach from=$chunk key=field item=value}
            <tr>
              <th>{$field}</th>
              <td><div>{$value}</div></td>
            </tr>
          {/foreach}
        </table>
      </div>
	{foreachelse}
	  <div class='emptyFieldMsg'>Error: no se encontró información de este elemento</div>
    {/foreach}

    {if isset($snippet_extraHTML)}{$snippet_extraHTML}{/if}
</div>