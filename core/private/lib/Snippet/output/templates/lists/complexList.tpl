<style type="text/css">
	.complexList_title{
		margin-bottom:40px;
		text-align:right;
	}
	.complexList_group{
		margin-top:15px;
	}
	.complexList_group > DIV{
		display:none; /**/
		margin:0px;
		padding:3px;
		border:solid 1px #706060;
		border-top:none;
	}
	.complexList_groupBody > DIV{
		padding:2px 3px;
		font-weight:bold;
	}
	.complexList_groupBody A, .complexList_groupBody SPAN{
		font-weight:normal;
		text-decoration:none;
	}
	.complexList_preview{
		background:#ffffff;
	}
	.complexList_groupHeader{
		margin:0px;
		margin-top:10px;
		padding:3px;
		padding-bottom:5px;
		border:solid 1px #cccccc;
		border-bottom:double 3px #908080;
		font-size:medium;
		font-family:Georgia, "Times New Roman", Times, serif;
		background: -webkit-gradient(linear, left top, left bottom, from(#e0e0e0), to(#8090a0));
		background: -moz-linear-gradient(top,  #e0e0e0,  #8090a0);
		filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#e0e0e0', endColorstr='#8090a0');
	}
	.complexList_group:hover .complexList_groupHeader{
		color:#660000;
		background: -webkit-gradient(linear, left top, left bottom, from(#c0c0c0), to(#8090b0));
		background: -moz-linear-gradient(top,  #c0c0c0,  #8090b0);
		filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#c0c0c0', endColorstr='#8090b0');
	}
	.complexList_expand{
		padding:0px !important;
		border:none !important;
		background: -webkit-gradient(linear, left top, left bottom, from(#8090b0), to(#e2e4ef));
		background: -moz-linear-gradient(top,  #c0c0c0,  #b0c0e0);
		filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#c0c0c0', endColorstr='#b0c0e0');
		color:#600000;
		font-size:16px;
		font-weight:bold;
		text-align:center;
		cursor:pointer;
	}
</style>


<h1 class='complexList_title'>Presupuestos Corporativos</h1>

{foreach from=$data key=code item=group}
  <div class='complexList_group' for='{$code}'>
	<H4 class='complexList_groupHeader'>{$group.name}</H4>
	<div class='complexList_groupBody'>
	  <div>
		{$membersName}:
		{foreach from=$group.members key=id item=name}
		  <a href='javascript:void();' class='complexList_members' for='{$id}'>{$name}</a> |
		{/foreach}
		<a href='javascript:void();' class='complexList_members' for='{$id}'>(agregar)</a>
	  </div>
	  {foreach from=$fields key=k item=v}
		{foreach $group.data[$k] key=id item=name}
		  <div class='complexList_property'>
			{$v}:
			{if $id}
			  <a href='javascript:void();' class='complexList_{$k}' for='{$id}'>{$name}</a>
			{else}
			  <span>{$name}</span>
			{/if}
		  </div>
		{/foreach}
	  {/foreach}
	</div>
	<div class='complexList_preview listPreLoad'></div>
	<div class='complexList_expand'>&dArr;</div>
  </div>
{/foreach}