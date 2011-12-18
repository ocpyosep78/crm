{literal}
<script type='text/javascript'>
	window.addEvent('domready', function(){
		$('debugStats').onclick = function(){
			if( $('debugFrame').style.display == 'block' ){
				$('debugFrame').style.display = 'none';
			}else{
				$('debugFrame').src = 'index.php?stats';
				$('debugFrame').style.display = 'block';
			};
		};
	});
</script>
{/literal}

<div id='debugHeader'>
	<div>
	  <div id="openDebug" onclick="$('debugHeader').removeClass('dbgHid');"></div>
	  <img id='debugStats' src='app/images/stats.gif' alt='extended info' />
	  <div style='float:left; width:600px;' onclick="$('debugHeader').addClass('dbgHid');">
		Debugger <span style="color:#0000a0; cursor:pointer;">(click para minimizar)</span>
		<br />
		{foreach from=$Page->develMsgs item=develMsg}{$develMsg}<br />{/foreach}
	  </div>
	</div>
	{if $Page->errMsgs}<hr style='clear:both;' />{/if}
	{foreach from=$Page->errMsgs item=errMsg}{$errMsg}<br />{/foreach}
	<br />
	<br />
	<iframe id='debugFrame'></iframe>
</div>