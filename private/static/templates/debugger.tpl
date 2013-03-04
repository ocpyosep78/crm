<div id='debugHeader'>
  <div>
	<div id="openDebug"></div>
	<img id='debugStats' src='{$IMAGES_URL}/stats.gif' alt='extended info' />
	<ul id="debuggerbox">
	  <li>Debugger <span style="color:#0000a0; cursor:pointer;">(click to minimize)</span></li>

	  <li>Module (id: {$pagestate.tree.module.id}): {$pagestate.tree.module.module}</li>
	  <li>Page (id: {$pagestate.page.id}): {$pagestate.page.model}:{$pagestate.page.action} (a.k.a. {$pagestate.page.alias})</li>
	</ul>
  </div>

  {if $errMsgs}<hr style='clear:both;' />{/if}

  {foreach from=$errMsgs item=msg}{$msg}{/foreach}

  <iframe id='debugger'></iframe>
</div>