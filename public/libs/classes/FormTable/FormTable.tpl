{assign var=FT value=$Builder->get('FormTable')}

{if $FT->preText}
	<div class='FormTablePreText'>
		{foreach from=$FT->preText item=preText}<p>{$preText}</p>{/foreach}
	</div>
{/if}

{if $FT->hasFrame()}
	<fieldset class='FormTableBox'>
		<legend>{$FT->frameTitle}</legend>
{else}
	<div class='FormTableBox'>
{/if}

<form {foreach from=$FT->formAtts key=att item=value} {$att}="{$value}"{/foreach}>
	<table{foreach from=$FT->tableAtts key=att item=value} {$att}='{$value}'{/foreach}>
		{foreach from=$FT->data item=x}
			<tr{if $x.hidden} style='display:none;'{/if}{if $x.atts.id} id='row_{$x.atts.id}'{/if}>
				{if $x.type == 'separator'}
					<td colspan='2'><hr class='sep' /></td>
				{elseif $x.type == 'title'}
					<th colspan='2' class='FormTableTitle'>{$x.value}</th>
				{elseif $x.type == 'note'}
					<th colspan='2' class='FormTableNote'
						{if $x.atts.id} id='field_{$x.atts.id}'{/if}>{$x.value}</th>
				{elseif $x.type == 'free'}
					{$x.value}
				{else}
					{if $x.type == 'submit' || $x.type == 'button'}
						<td colspan='2' class='FormTableSubmitLine'>{$x.field}
					{else}
						<th{if $x.atts.id} id='field_{$x.atts.id}'{/if}>{$x.field}</th>
						<td>
					{/if}
					{if $x.type == 'row'}
						{$x.value}
					{elseif $x.type == 'area'}
						<textarea{foreach from=$x.atts key=att item=val} {$att}='{$val}'{/foreach}
						>{$x.atts.value}</textarea>
					{elseif $x.type == 'combo'}
						<select{foreach from=$x.atts key=att item=val} {$att}='{$val}'{/foreach} class='input'>
							{foreach from=$x.value key=value item=text}
								<option value='{$value}'{if $x.selected == $value} selected='selected'{/if}>
									{$text}
								</option>
							{/foreach}
						</select>
					{else}
						<input type='{$x.type}' {foreach from=$x.atts key=att item=val} {$att}='{$val}'{/foreach} />
					{/if}
					</td>
				{/if}
			</tr>
		{/foreach}
	</table>
</form>

{if $FT->hasFrame()}
	</fieldset>
{else}
	</div>
{/if}