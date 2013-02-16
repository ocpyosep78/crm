{foreach from=$tabs key=k item=v}
	<div for='{$k}'{if $k eq $tab} class='selectedTab'{/if} title='{$v.name}'>{$v.name|truncate:40:'...'}</div>
{/foreach}