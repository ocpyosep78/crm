<div class='bigTools'>
  {foreach from=$tools key=code item=tool}
	<div btn='{$code}' title='{$tool.name} {$name|lower}'{if $tool.disabled} class='bigToolDisabled'{/if}>
	  <img src='{$SNIPPET_IMAGES}/buttons/{$code}.png' alt='{$tool.name}' />
	</div>
  {/foreach}
</div>