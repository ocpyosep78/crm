<div id='debugHeader'>
  <div>
	<div id="openDebug"></div>
	<img id='debugStats' src='{$BBURL}/app/images/stats.gif' alt='extended info' />
	<ul id="debuggerbox">
	  <li>Debugger <span style="color:#0000a0; cursor:pointer;">(click para minimizar)</span></li>

	  <li>Módulo: {$pagestate.areaid} ({$pagestate.area.name})</li>
	  <li>Página: {$pagestate.pageid} ({$pagestate.page.name})</li>
	</ul>
  </div>

  {if $errMsgs}<hr style='clear:both;' />{/if}

  {foreach from=$errMsgs item=msg}{$msg}{/foreach}

  <iframe id='debugger'></iframe>
</div>