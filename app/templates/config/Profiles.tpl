<div id='listToConfig' class='data'>

	<h2>Perfiles de Usuario</h2>
	<hr />

	{foreach from=$profiles key=id item=profile}
	  <div class='listRow' for='{$id}'>
		<a href='javascript:void();'>{$profile}</a>
		<img src='app/images/buttons/delete.png' class='delItem' title='eliminar' />
		<img src='app/images/buttons/edit.png' class='editItem' title='editar' />
	  </div>
	{/foreach}
	
	<input type='text' id='newProfile' value='' /><br />
	<input type='button' id='btn_newProfile' value='Crear Perfil' />
	
</div>

{include file='config/_permits.tpl'}