<div class='bigTools'>
  {foreach from=$tools key=code item=tool}
	<div btn='{$code}' title='{$tool} {$name|lower}'>
	  <img src='{$SNIPPET_IMAGES}/buttons/{$code}.png' alt='{$tool}' />
	</div>
  {/foreach}
</div>