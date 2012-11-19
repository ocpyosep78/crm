{$bigTools}

<div class='commonListWrapper'>
  <div class='listTitles'>
	{foreach from=$titles item=title}
	  {if $title != $primary}
		<div for='{$title}' title='{$title}'>{$title|truncate:20:'...'}</div>
	  {/if}
	{/foreach}

	<div class='tablesearch' style='text-align:right;'><input type='text' /></div>
  </div>

  <div style='clear:both;'></div>

  <div class='commonListInnerWrapper'>
	<div class='listWrapper'>
		{include file="{$SNP_TEMPLATES}/snippets/commonList.inner.tpl"}
	</div>
  </div>
</div>