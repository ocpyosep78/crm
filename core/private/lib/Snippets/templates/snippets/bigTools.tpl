<div class='bigTools'>
  {foreach from=$bigButtons key=code item=button}
	<div btn='{$code}' title='{$button} {$name|lower}'>
	  <img src='{$SNP_IMAGES}/buttons/{$code}.png' alt='{$button}' />
	</div>
  {/foreach}
</div>