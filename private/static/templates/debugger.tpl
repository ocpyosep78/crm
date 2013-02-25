<div id='debugHeader'>
  <div>
	<div id="openDebug"></div>
	<img id='debugStats' src='{$IMAGES_URL}/stats.gif' alt='extended info' />
	<ul id="debuggerbox">
	  <li>Debugger <span style="color:#0000a0; cursor:pointer;">(click para minimizar)</span></li>

	  <li>Módulo: {$pagestate.tree.module.model}:{$pagestate.tree.module.page}</li>
	  <li>Página: {$pagestate.info.model}:{$pagestate.info.page}</li>
	</ul>
  </div>

  {if $errMsgs}<hr style='clear:both;' />{/if}

  {foreach from=$errMsgs item=msg}{$msg}{/foreach}

  <iframe id='debugger'></iframe>
</div>