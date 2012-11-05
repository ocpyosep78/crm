{*
	/**
	 *	chatCheck is an auxiliar iframe whose original src is CHAT_SERVICE_PATH/?chatCheck
	 *	chatActivity is the actual iframe where the observer will run if loading succeeds
	 *
	 * chatCheck should be hidden, while chatActivity should be correctly styled: hidden by
	 * default, and shown if it is of class 'ready' (which this script ensures upon loading).
	 *
	 * This script uses jQuery if present, but doesn't depend on it.
	 *
	 ************************************   IMPORTANT   ************************************
	 *
	 * - $CHAT_ADDRESS needs to be defined for Smarty vars, as the url to the chat engine.
	 * - $URL needs to be defined as any address to this app's domain (a page that just dies
	 * with no output is recommended, it only has to be on this app's domain and load).
	 *
	 * If the service is down, both frames (chatActivity and auxiliary frame chatCheck) will
	 * be discarded (removed). If you want something else to be done (i.e. loading a page of
	 * your own alerting that the chat service is down or the server could not be reached),
	 * just replace dispose() function content with your own.
	 *
	 */
*}

{literal}
<script type="text/javascript">

	function addCSSRule(selectorText, declarations) {
		// document.styleSheets support required
		if (!document.styleSheets) return false;

		// Create element and append it
		J('head').append(J('<style />', {'type': 'text/css'}));

		// Insert rules in the new styleSheet
		var styleSheet = document.styleSheets[document.styleSheets.length - 1];
		if (styleSheet && styleSheet.insertRule) {
			styleSheet.insertRule(selectorText + ' { ' + declarations + ' }', styleSheet.cssRules.length);
		} else if (styleSheet.addRule) {
			styleSheet.addRule(selectorText, declarations);
		}
	}

	addCSSRule('#chatActivity', 'position:fixed;right:0px;bottom:0px;');
	addCSSRule('#chatActivity.ready', 'height:78px;width:122px;');

</script>
{/literal}



<iframe name='chatActivity' id='chatActivity' scrolling="no" frameborder="0" width="0" height="0"
	src='{$CHAT_ADDRESS}/?host&observer'></iframe>
<iframe name='chatCheck' id='chatCheck' scrolling="no" frameborder="0" width="0" height="0"
	src='{$CHAT_ADDRESS}/?chatCheck&url={$URL}?chatCheck'></iframe>



{literal}
<script type="text/javascript">

	var chatCheck = window.frames['chatCheck'];
	var observer = J('#chatActivity');

	if (chatCheck && observer) {
		var cnt = 0;
		var testChat = function(){
			try{										// Error will stop when chat observer confirms
				chatCheck.location.toString();			// it has loaded, by changing chatCheck's url
				var testError = chatCheck.location;		// to the same domain as the application
				observer.addClass('ready');				// <= No error? Then it succeeded!
				return delChatCheck();
			}catch(e){}
			if( ++cnt < 10) setTimeout(testChat, 2000);	// Let's try again
			else dispose();								// 3 tries and counting, so it failed.
		};

		var delChatCheck = function(){
			J('#chatCheck').remove();
		};

		var dispose = function(){
			observer.remove();
			delChatCheck();
		};

		setTimeout(testChat, 1000);		// Let's try to avoid unneeded cross-domain warnings
	}

</script>
{/literal}