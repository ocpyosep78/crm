{literal}
<style type="text/css">
	#tabs{
		float:left;
		heigth:21px;
		border-bottom:solid 1px #000000;
		overflow:hidden;
	}
	.tab{
		float:right;
		width:100px;
		padding:1px 12px;
		margin-bottom:-23px;
	}
	.tab:hover{
		cursor:pointer;
	}
	.tabText{
		height:19px;
		padding-right:10px;
		padding-top:4px;
		background:rgb(194,193,193);
		color:#000000;
		text-align:center;
		font-weight:bold;
		white-space:nowrap;
	}
	.tab:hover .tabText{
		background:rgb(94,104,110);
		color:#ffffff;
	}
	.selectedTab .tabText, .selectedTab:hover .tabText{
		background:rgb(97,111,133);
		color:#ffffa0;
	}
	.selectedTab:hover .tabText{
		color:#ffff60;
		cursor:default;
	}
	.tab:before, .tab:hover:before{
		display:block;
		position:absolute;
		float:left;
		height:23px;
		width:16px;
		margin-left:-16px;
		background:url(app/images/buttons/tabs.png) no-repeat;
		background-color:none;
		content:'';
	}
	.tab:before{
		background-position:0px -61px;
	}
	.tab:hover:before{
		background-position:0px -31px;
	}
	.selectedTab:before{
		background-position:0px 0px !important;
	}
	.tab:after, .tab:hover:after{
		display:block;
		float:right;
		position:relative;
		top:-23px;
		height:23px;
		width:24px;
		margin-right:-18px;
		background:url(app/images/buttons/tabs.png) no-repeat;
		background-color:none;
		content:'';
	}
	.tab:after{
		background-position:-85px -61px;
	}
	.tab:hover:after{
		background-position:-85px -31px;
	}
	.selectedTab:after{
		background-position:-85px -0px !important;
	}
	#tabs HR{
		clear:both;
		position:relative;
		top:-23px;
		left:-1px;
		margin:0px -3px;
		border:solid 1px #000000;
		border-top:none;
	}
	#hideTabsBottom{
		position:relative;
		top:-23px;
		height:23px;
	}
</style>
{/literal}

<div id='tabs'>
  {foreach from=$tabs key=key item=item}
	<div class='tab{if $tab eq $key} selectedTab{/if}' for='{$key}'><div class='tabText'>{$item}</div></div>
  {/foreach}
  <div id='hideTabsBottom'></div>
</div>
<div style='clear:both;'></div>

<div>{include file=config/`$tab`.tpl}</div>