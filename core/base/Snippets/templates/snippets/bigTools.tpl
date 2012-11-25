<div class='bigTools'>
  {foreach from=$buttons key=action item=button}
	<div btn='{$action}' title='{$button.title}'>
	  <img src='{$button.icon}' alt='{$button.title}' />
	</div>
  {/foreach}
</div>