{$comboList}

{if $isSelf}
	{if $Permits->can('editAccInfo')}
	  <a id='editAccInfo' class='infoPageTools' href='javascript:void(0);'>Editar Datos Personales</a>
	{/if}
{else}
	{if $Permits->can('editUsers')}
	  <a id='editUsers' class='infoPageTools' href='javascript:void(0);' for='{$userID}'>Editar Usuario</a>
	{/if}
{/if}
  
<div class='infoBlocks'>
	{foreach from=$blocks item=block}<div>{$block}</div>{/foreach}
	<img class='userPic' alt='{$userID}' title='{$userID}' src='{$userImg}' />
</div>