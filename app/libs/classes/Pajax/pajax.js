var Pajax = {
	submitForm: function(form, handler, timeOut){
		if( !(form=$(form)) || !handler ) return !!showStatus('Pajax error: missing params');
		// Dirty, ugly, unefficient... IE compatible code for submitting within dynamic iframe!
		var div = document.createElement('DIV');
		div.style.display = 'none';
		div.innerHTML = "<iframe id='pajaxFrame' name='pajaxFrame'></iframe>";
		document.body.appendChild( div );
		var frame = $('pajaxFrame');
		// Add marker to our form and redirect it
		var hiddenInput = document.createElement('INPUT');
		hiddenInput.type = 'hidden';
		hiddenInput.name = 'pajax';
		hiddenInput.value = handler||'';
		form.appendChild( hiddenInput );
		form.target = 'pajaxFrame';
		var thisInterval = setInterval(function(){
			var fDoc = window.frames['pajaxFrame'].document;
			if( !fDoc.body || !fDoc.body.innerHTML ) return;	// Still waiting...
			var pjx = fDoc.getElementsByTagName('P');
			if( !pjx || pjx.length != 1 ){						// Not a valid answer
				showStatus( 'Ocurrió un error al procesar el formulario.' );
				var errStr = fDoc.body.innerHTML;
				clearFrame();
				throw( errStr );
				return;
			};
			var cmds = pjx[0].getElementsByTagName('B');
			for( var x in cmds ) if( cmds[x].nodeType == 1 ) eval(cmds[x].innerHTML);
			clearFrame();
		}, 100);
		var thisTimeout = setTimeout(function(){
			showStatus('Connection timeout');
			clearFrame();
		}, (timeOut||30)*1000);
		var clearFrame = function(){
			clearInterval( thisInterval );
			clearTimeout( thisTimeout );
			form.removeChild( hiddenInput );
			delete( hiddenInput );
			document.body.removeChild( div );
			delete( div );
		};
//		$(form).action = '?' + (new Date).getMilliseconds();
		return $(form).submit() && false;
	}
};