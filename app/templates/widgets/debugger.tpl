{literal}
<style type="text/css">
	#debugFrame{ display:none; clear:both; height:500px; width:97%; margin:10px 3px; border:solid 1px #ffffff; background:#7C9AAE; }
	#debugStats{ float:left; cursor:pointer; margin-right:5px; }
	#debugHeader{position:fixed;left:5px;bottom:5px;background-color:#9cbace;border:solid 1px #990000;color:#990000;padding:3px 5px;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:9px;font-weight:bold;opacity:0.85;filter:alpha(opacity=85);z-index:5000;}
	#debugHeader div{color:#000000;padding:0px 1px 2px 1px;}
	#debugHeader span{color:#004000;}
	#errMsgs{position:fixed;top:3px;left:3px;background:#e0e0e0;padding:3px;border:solid 1px #000000;z-index:250;}
	.sqlError{color:#993300;text-indent:5px;}
	#debugHeader #openDebug{display:none;}
	#debugHeader.dbgHid{height:16px;width:16px;margin:0px;padding:0px;overflow:hidden;}
	#debugHeader.dbgHid #openDebug{background-image:url(app/images/arrow_green_rigth.png);background-color:#000000;position:absolute;left:0px;top:0px;height:16px;width:16px;z-index:300;cursor:pointer;display:block;}
</style>

<script type='text/javascript'>
	J(function(){
		J('#debugStats').click(function(){
			J('#debugFrame').toggle();

			J('#debugFrame:visible').length
				|| $('debugFrame')._src('index.php?stats');
		});
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