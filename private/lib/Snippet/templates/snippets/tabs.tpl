<div class='tabs'>
  <ul>
	{foreach from=$tabs item=tab}
	  <li><a href="#{$tab.id}_{$snp_params['groupId']}"><span>{$tab.title}</span></a></li>
	{/foreach}
  </ul>

  {foreach from=$tabs item=tab}
	<div id="{$tab.id}_{$snp_params['groupId']}" rel="{$tab.id}">
	  <div class='snp_loading'><img src="{$SNP_IMAGES}/loading.gif" /></div>
	</div>
  {/foreach}
</div>