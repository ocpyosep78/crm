{$content}

{* JS on domready *}
{if isset($js)}
  <script type="text/javascript">
	$(function(){
		{foreach from=$js item=code}
			{$code};
		{/foreach}
	});
  </script>
{/if}