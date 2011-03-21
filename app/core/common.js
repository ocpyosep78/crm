$_ = {style:{}};
$E = {addEvent: function(){}, setStyle: function(){}};

/* Just make sure these exist from the start (no need to throw errors if they don't) */
var showLoading = function(){};

function raise( msg ){
	var caller = (arguments.callee.caller && arguments.callee.caller.name)
		? arguments.callee.caller.name + ' '
		: '';
	return DEVELOPER_MODE ? !!alert( caller + 'error: ' + (msg||'') ) : false;
};

if( IN_FRAME ) window.addEvent('domready', function(){ Frames.initialize(); });
Frames = {
	frames: [],
	initialize: function(){		/* Called by an actual frame's onload event */
		var that = this, d = document, id = window.frameElement.uID;
		function close(){ window.parent.Frames.garbageCollect( id ); }
		$(d.body).addEvent('escape', close);
		$('FrameCloseButton').addEvent('click', close);
		this.fixDimensions();
		$(window.parent).addEvent('resize', function(){ that.fixDimensions(); });
		window.frameElement.style.visibility = 'visible';
	},
	loadPage: function( href ){
		var id = this.frames.length, d = document;
		var ifr = this.frames[id] = d.body.appendChild( d.createElement('IFRAME') );
		ifr.className = 'ifrForShowPage';
		ifr.src = href + '&iframe';
		ifr.uID = id;
	},
	fixDimensions: function(){
		var h = Math.round($(window.frameElement).getHeight() * 0.8);
		var w = Math.round($(window.frameElement).getWidth() * 0.9);
		$('frameArea').setStyle('height', h);
		$('frameArea').setStyle('width', w);
		$('frameTitle').setStyle('width', w);
		$('frameContent').setStyle('marginTop', h/8 - 30);
		$('statusMsgs').setStyle('width', w + 10);
	},
	close: function(msg, code){
		if( msg ) window.parent.showStatus(msg, code||0);
		window.parent.Frames.garbageCollect( window.frameElement.uID );
	},
	garbageCollect: function( id ){
		if( this.frames[id] ) document.body.removeChild( this.frames[id] );
	}
}

var getPage = function(){
	var args = Array.from( arguments );
	var ctrl = (typeof(args[0]) == 'object') ? (args[0].ctrlKey|args.shift().control) : false;
	return (ctrl ? xajax_showPage : xajax_getPage).apply(null, args);
}
var loadContent = xajax_loadContent;	/* Calls a page function to update content */
var showPage = xajax_showPage;			/* Loads a page within an iframe */

var IniParams = {
	params: null,
	set: function( data ){
		this.params = Array.from( data );
	},
	get: function(){
		var params = Array.from( this.params );
		delete( this.params );
		return params||{};
	}
};

function iniPage( name ){
	/* Call page's engine */
	try{
		if(window['ini_'+name]) window['ini_'+name].apply(window['ini_'+name], IniParams.get());
		enableComboList();
	}catch(e){
		if( DEVELOPER_MODE ) test( e );
		return false;
	};
	/* Autofix browse buttons and calendar inputs */
	fixBrowseButton( document.documentElement , 'Examinar...' , 'browse' );
	globalApplyCalendar();
};

function hideMenu(){
	if( !$('showMenu') || !$('hideMenu') ) return;
	(function(){
		new Fx.Tween('menuDiv',
			{property:'opacity', duration:400, fps:50, onStart:function(){
				this.set(1);
			}, onComplete:function(){
				$('hideMenu').style.display = 'none';
				this.set('display', 'none');
				$('showMenu').style.display = 'inline';
				$('main_menu').setStyle('width', 'auto');
				fixTableHeader();
			} }
		).start(0.3);
	}).apply( $('hideMenu') );
};
function showMenu(){
	if( !$('showMenu') || !$('hideMenu') ) return;
	(function(){
		new Fx.Tween(
			'menuDiv',
			{property:'opacity', duration:400, fps:50, onStart:function(){
				this.set(0.3);
				$('showMenu').style.display = 'none';
				this.set('display', 'block');
				$('main_menu').setStyle('width', 1);
				$('hideMenu').style.display = 'inline';
				fixTableHeader();
			} }
		).start(1);
	}).apply( $('showMenu') );
};

/*************************************************************************************************/
/***************************************** G E N E R A L *****************************************/
/*************************************************************************************************/

function in_array( needle, hStack ){    /* Emulate PHP's in_array for both arrays and objects */
	if( typeof(hStack) == 'object' ) for( var i in hStack ) if( hStack[i] == needle ) return true;
};

function validEmail( email ){
   return !!/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/.test(email);
};

function stristr(x, y){
	var pos = (x||'').toLowerCase().indexOf((y||'').toLowerCase());
	return pos != -1 ? (x||'').substr(pos) : '';
};

function count(a){
	return (a||[]).length || 0;
};

function validateTimeInput(obj, attToEdit){
	var p = $(obj).value.split(':');
	var val = p[0].fill(2, 0, true) + ':' + (p[1]||'').fill(2, 0, true);
	var ret = val.test(/^(2[0-3]|[01]\d):[0-5]\d$/) ? val : '';
	if( ret && typeof(attToEdit) == 'string' ) $(obj)[attToEdit] = ret;
	return ret || false;
};

function readTextArea( ta ){
	return $(ta) ? $(ta).value || $(ta).innerHTML || $(ta).text || '' : '';
};

function silentXajax(func, params){
	var currentLoadingFunction = xajax.loadingFunction;
	xajax.loadingFunction = function(){};
	xajaxWaitCursor = false;
	(window['xajax_'+func]||function(){}).apply(window, params||[]);
	xajax.loadingFunction = currentLoadingFunction;
	xajaxWaitCursor = true;
};

/*************************************************************************************************/
/************************************* A P P L I C A T I O N *************************************/
/*************************************************************************************************/

function switchNav(e, obj){
	if( e && (e.control||e.ctrlKey) ) return;
	for( var i=0, navs=$$('.navCurrMod'), nav ; nav=navs[i] ; i++ ) nav.className = 'navMod';
	$(obj).className = 'navCurrMod';
};

function fixBrowseButton( oBox , sText , sButtonClass , sTextClass ){
	var oInputs = oBox && oBox.getElementsByTagName && oBox.getElementsByTagName('INPUT');
	if( !oInputs || !oInputs.length ) return;
	for( var i=0, oInput, aFiles=[] ; oInput=oInputs[i] ; i++ ){
		if( (oInput.getAttribute('type')||'').toLowerCase() == 'file' ) aFiles.push( oInput );
	};
	if( !aFiles.length ) return;
	var oBrowserBox = document.createElement( 'DIV' );
	var oHiddenBox = document.createElement( 'DIV' );
	var oText = document.createElement( 'INPUT' );
	if( /*@cc_on!@*/false ){		// IE
		var oButton = document.createElement( 'BUTTON' );
		oHiddenBox.style.top = '0px';
	}else{
		var oButton = document.createElement( 'INPUT' );
		oButton.type = 'button';
		oHiddenBox.style.top = '-5px';
		oText.setAttribute( 'type' , 'text' );
	};
	if( navigator.userAgent.toLowerCase().indexOf('chrome') > -1 ){
		oButton.style.top = '0px';
		oBrowserBox.style.left = '2px';
		oText.setAttribute( 'type' , 'text' );
	};
	oButton.className = (sButtonClass||'');
	oButton.setAttribute( 'value' , (sText||'') );
	oButton.style.margin = '0px 2px';
	oButton.style.width = '90px';
	oHiddenBox.style.position = 'absolute';
	oHiddenBox.style.left = '0px';
	oHiddenBox.style.opacity = '0';
	oHiddenBox.style.filter = 'alpha(opacity=0)';
	oBrowserBox.appendChild( oText );
	oBrowserBox.appendChild( oButton );
	oBrowserBox.appendChild( oHiddenBox );
	oText.className = (sTextClass||'');
	oText.setAttribute( 'disabled' , 'disabled' );
	oText.style.margin = '0px';
	oText.style.cursor = 'pointer';
	oBrowserBox.style.display = 'inline';
	oBrowserBox.style.position = 'relative';
	oBrowserBox.style.whiteSpace = 'nowrap';
	for( var i=0, oFile ; oFile=aFiles[i] ; i++ ){
		oNewBrowser = oBrowserBox.cloneNode( true );
		oFile.onchange = function(){
			this.parentNode.parentNode.firstChild.value = this.value.split(/[\\\/]/).pop();
		};
		oFile.parentNode.insertBefore( oNewBrowser , oFile );
		oNewBrowser.getElementsByTagName( 'DIV' )[0].appendChild( oFile );
		oNewBrowser.parentNode.style.overflow = 'hidden';
	};
};

/*************************************************************************************************/
/******************************************* T E X T S *******************************************/
/*************************************************************************************************/

function sprintf( str ){
	if( !str ) return '';
	aStr = str.split( '%s' );
	if( aStr.length != arguments.length ) return str;	// Wrong parameters count
	for( var i=1, resStr=aStr[0] ; i<arguments.length ; i++ ) resStr += arguments[i]+aStr[i];
	return resStr;
};

/***********************************************************************************************/
/********************************* C O M B O   S H O R T C U T S *******************************/
/***********************************************************************************************/

function addOption( oCombo , sValue , sText , bReset ){
	if( bReset ) oCombo.options.length = 0;
	var oOpt = document.createElement( 'OPTION' );
	oOpt.value = sValue;
	oOpt.text = sText;
	oCombo.options.add( oOpt );
	if( bReset ) oCombo.options[0].setAttribute( 'selected' , 'selected' );
};
function getOption(oCombo, sVal, sBy){	// sBy: 'value' (default) or 'text'
	for( var i=0, opt ; opt=oCombo.options[i] ; i++ ) if( opt[(sBy||'value')] == sVal ) return i;
	return null;
};
function remOption(oCombo, sVal, sBy){		// sBy: 'index' (default), 'selected', 'value' or 'text'
	if( !sBy || sBy == 'index' ) return ( oCombo.options[sVal] ) ? oCombo.remove(sVal) : false;
	if( sBy == 'selected' && oCombo.selectedIndex ) oCombo.remove( oCombo.selectedIndex );
	for( var i=0 ; i<oCombo.options.length ; i++ ){
		if( sBy == 'value' && oCombo.options[i].value == sVal ) oCombo.remove(i);
		else if( sBy == 'text' && oCombo.options[i].text == sVal ) oCombo.remove(i);
	};
};
function selectOption(oCombo, sVal, sBy){	// sBy: 'index' (default), 'value' or 'text'
	for( var i=0, opt ; opt=oCombo.options[i] ; i++ ) opt.removeAttribute('selected');
	if( sBy && sBy != 'index' ) selectOption(oCombo, getOption(oCombo, sVal, sBy));
	else if( sVal >= 0 && sVal < oCombo.options.length ) oCombo.selectedIndex = sVal;
};
function getSelected( oCombo , sBy ){
	if( oCombo && oCombo.options && oCombo.selectedIndex >= 0 ){
		return oCombo.options[oCombo.selectedIndex][(sBy||'value')];
	}else return null;
};
function seekOption( oCombo, str, sBy ){
	for( var i=0, opt ; opt=oCombo.options[i] ; i++ ){
		if( !opt[sBy||'text'].toLowerCase().trim().indexOf(str.toLowerCase().trim()) ) return i;
	};
};
function checkFormElement(form, rads, val){		/* Check a radio button by val */
	oRads = (typeof(form) == 'string' ? document.forms[form][rads] : form[rads]) || {};
	for( var i=0, rad ; rad=oRads[i] ; i++ ) rad.checked = !!(rad.value == val);
};
function getRadioValue( rads ){
	if( typeof(rads) == 'string' ) rads = document[rads] || {};
	for( var i=0, rad ; rad=rads[i] ; i++ ) if( rad.checked ) return rad.value;
};

/*************************************************************************************************/
/******************************************* P O P U P *******************************************/
/*************************************************************************************************/

function popup( mylink , windowname ){
	var preferencias = '', href;
	preferencias += 'width='+(window.screen.width*90/100)+',';
	preferencias += 'height='+(window.screen.height*80/100)+',';
	preferencias += 'scrollbars=yes,';
	preferencias += 'screenX='+(window.screen.width*5/100)+',';
	preferencias += 'screenY='+(window.screen.height*5/100);
	if( !window.focus ) return true;
	if( typeof(mylink) == 'string' ) href = mylink;
	else href = mylink.href;
	window.open( href , (windowname||null) , preferencias );
	return false;
};

function reDirectMe( path , post ){
	if( !path ) path = location.href;
	if( !post ) location.href = path;
	else{
		var frm = document.createElement('FORM');
		frm.action = path;
		frm.method = 'POST';
		var params = post.split( '&' );
		for( var i=0 ; i<params.length ; i++ ){
			var parts = params[i].split('=');
			var newParam = document.createElement( 'INPUT' );
			newParam.setAttribute( 'type' , 'hidden' );
			newParam.setAttribute( 'name' , parts[0] );
			newParam.setAttribute( 'value' , (parts[1]||'') );
			frm.appendChild( newParam );
		};
		BODY.appendChild( frm );
		frm.submit();
		frm.parentNode.removeChild( frm );
	};
};

function submitFormOnPopup( popupName, frmName, url, atts ){
/***************
** Author: dbarreiro (diego.bindart@gmail.com)
** Opens a popup and submits an existing form into that new window
****************/
	if( !document.forms[frmName] ) return alert('submitFormOnPopup failed: Form not found!');
	window.open('',popupName,(atts||'location=NO,menubar=NO,toolbar=NO'));
	var tmpFrm = document.forms[frmName].cloneNode( true );
	tmpFrm.setAttribute('action', url);
	tmpFrm.setAttribute('target', popupName);
	document.body.appendChild( tmpFrm );
	tmpFrm.submit();
	document.body.removeChild( tmpFrm );
	delete( tmpFrm );
};

/*************************************************************************************************/
/****************************************** E V E N T S ******************************************/
/*************************************************************************************************/

/**
* Returns target of the event
* from http://www.quirksmode.org/js/events_properties.html
**/
function getEventTarget( e ){
	var targ = (e=e||window.event||{}).target||e.srcElement||{};
	return ( targ.nodeType == 3 ) ? targ.parentNode : targ;
};
function stopEvent( e ){
	if( !e ) e = window.event || {};
	if( e.stopPropagation ) e.stopPropagation();
	else e.cancelBubble = true;
};
function getKeyCode( e ){
	if( !e ) e = window.event || {};
	var keyCode = null;
	if( e.keyCode ) keyCode = e.keyCode;		// IE
	else if( e.which ) keyCode = e.which;		// NS4
	else if( e.charCode ) keyCode = e.charCode;	// NS 6+, Mozilla 0.9+
	return keyCode;
};
function checkEnter( e , fReturn , fEscape ){
	var keyCode = getKeyCode( e );
	if( keyCode == 13 && fReturn ) return fReturn( getEventTarget(e) );
	else if( keyCode == 27 && fEscape ) return fEscape( getEventTarget(e) );
	return true;
};

/*************************************************************************************************/
/*********************************** D A T E   H A N D L I N G ***********************************/
/*************************************************************************************************/

function jsDate2MySqlDate( now ){
	return (now=now||(new Date())).getFullYear() + '-' +
		((now.getMonth()+1)+'').fill(2,'0',true) + '-' +
		(now.getDate()+'').fill(2,'0',true);
};

/*************************************************************************************************/
/********************************************* C S S *********************************************/
/*************************************************************************************************/

function addCSSRule(selectorText, declarations) {
	// document.styleSheets support required
	if( !document.styleSheets ) return false;
	// Create element and append it
	var styleElement = document.createElement('STYLE');
	styleElement.type = 'text/css';
	document.getElementsByTagName('HEAD')[0].appendChild( styleElement );
	// Check we're ok this far
	if( !document.styleSheets.length ) return false;
	// Insert rules in the new styleSheet
	var styleSheet = document.styleSheets[document.styleSheets.length - 1];
	if( styleSheet.insertRule ){
		styleSheet.insertRule(selectorText + ' { ' + declarations + ' }', styleSheet.cssRules.length);
	}
	else if( styleSheet.addRule ){
		styleSheet.addRule(selectorText, declarations);
	};
};

function db_isClass( oObj , sClass ){
	if( typeof(oObj) != 'object' || typeof(sClass) != 'string' || !sClass ) return false;
	return !!( oObj.className.match(new RegExp('\\b'+sClass+'\\b','i')) );
};
function db_addClass( oObj , sClass ){
	if(!oObj.className.match(new RegExp('\\b'+sClass+'\\b','i'))) oObj.className += ' '+sClass;
};
function db_remClass( oObj , sClass ){
	oObj.className = oObj.className.replace(new RegExp('\\b'+sClass+'\\b','i'),'').trimX();
	if( !oObj.className ) oObj.removeAttribute( 'class' );
};

function highLight(obj, color, markedColor){
	$(obj).highlight(color, markedColor||'#dfffbf');
	if( $(obj).hasClass('selectable') ){
		$(obj).removeClass('selectable');
		$(obj).addEvent('click', function(){
			$$('.selectedRow').forEach(function(row){ row.removeClass('selectedRow'); });
			$(this).addClass('selectedRow');
		});
	}
};

function highLightBox( obj ){
	obj.onfocus = function(){
		if( this.select ) this.select();
		db_remClass(this, 'input');
		db_addClass(this, 'inputFocused');
	};
	obj.onblur = function(){
		db_remClass(this, 'inputFocused');
		db_addClass(this, 'input');
	};
	obj.onfocus();
};

function applyCalendar(ini, end, noClear){
	var eIni = ((ini=$(ini)) && !ini.hasEpoch) ? new Epoch('ini', 'popup', ini) : null;
	var eEnd = ((end=$(end)) && !end.hasEpoch) ? new Epoch('end', 'popup', end) : null;
	(ini||{}).readOnly = (end||{}).readOnly = true;			// Disable direct editting
	(ini||{}).hasEpoch = (end||{}).hasEpoch = true;			// Flag this element
	if( eEnd ){
		eEnd.checkDate = function(){
			if( this.selectedDates[0].dateFormat() < ini.value ){
				return !!alert( 'La fecha final no puede preceder a la inicial.' );
			};
			return true;
		};
		end.onmousedown = function(){
			if( ini.value ) eEnd.goToMonth(ini.value.split('-')[0], ini.value.split('-')[1] - 1);
		};
	};
	var imgClear=$('calendarImgClear'), imgClearDis=$('calendarImgClearDisabled');
	if( noClear || !imgClear || !imgClearDis ) return;
	function clear(){ this.nextSibling.value = ''; };
	function addImg( el ){
		var isClearable = db_isClass(el, 'clearable');
		var oImgDiv = (isClearable ? imgClear : imgClearDis).cloneNode( true );
		if( isClearable ) oImgDiv.onclick = clear;
		oImgDiv.style.display = 'block';
		oImgDiv.id = '';
		el.parentNode.insertBefore(oImgDiv, el);
	};
	if( eIni ) addImg( ini );
	if( eEnd ) addImg( end );
};

function globalApplyCalendar(){
	$$('.calendar').forEach(function(cal){
		applyCalendar(cal, cal.getAttribute('calpair')||null );
	});
};

function validEmail( email ){
   return !!/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/.test(email);
};

/* Fix Table Titles */
function fixTableHeader( oTitlesBox, oTable, cached ){
	if( !arguments.length && typeof(arguments.callee.sets) == 'object' ){
		for( var i=0, set ; set=arguments.callee.sets[i] ; i++ ){
			fixTableHeader(set['titles'], set['table'], true);
		};
		return;
	};
	if( !arguments.callee.sets ) arguments.callee.sets = [];
	if( !oTitlesBox || !oTitlesBox.style || !oTable || !oTable.rows || !oTable.rows[0] ){
		if( oTitlesBox && oTitlesBox.style ) oTitlesBox.style.display = 'none';
		return;
	};
	if( oTitlesBox && oTitlesBox.style ) oTitlesBox.style.display = 'block';
	if( !cached ) arguments.callee.sets.push( {titles:oTitlesBox, table:oTable} );
	var nlCells = oTable.rows[0].cells;
	var nlTitles = oTitlesBox.getElementsByTagName('DIV');
	var isIE = !!(/*@cc_on!@*/false);
	var totalWidth = 10;
	for( var i=0, len=nlTitles.length, oCell, iWidth, title ; oCell=nlCells[i], oTitle=nlTitles[i] ; i++ ){
		iWidth = oCell.offsetWidth - 10;
		if( nlTitles[i+1] ) oTitle.style.width = iWidth + 'px';
		else oTitle.style.width = 'auto';
		totalWidth += iWidth;
		oTitle.style.display = 'block';
	};
	// Hide titles that do not match any column in the results table
	for( var j=i, oTitle ; oTitle=nlTitles[j] ; j++ ) oTitle.style.display = 'none';
};

function showAlerts(){
	alert('En construcción');
};

function xajaxSubmit(oForm, sFunc, showDisabled, atts){		/* Submit form through ajax */
	window['xajax_'+sFunc]( xajax.getFormValues(oForm, showDisabled), atts||[] );
};

function newTip(id, obj){
	var tip = document.createElement('DIV');
	tip.id = 'tip_' + id;
	if( obj ){
		$(obj.parentNode).addClass('Tip');
		obj.parentNode.insertBefore(tip, obj.nextSibling);
	}
	return tip;
};

function showTip(field, tip){
	clearTimeout( showTip.to );
	(showTip.sel||{}).innerHTML = '';
	if( !($field=$('tip_' + field)) ) throw('No existe el tip ' + field);
	$field.innerHTML = tip;
	showTip.to = setTimeout(function(){ $field.innerHTML = ''; }, 5000);
	showTip.sel = $field;
};
function FTshowTip( field, tip ){
	var $field = $(field), $tip = $('tip_'+field), $blur;
	if( !$tip ) throw( 'No existe el elemento ' + field );
	setTimeout( function(){	/* Blur preceeds focus, so make sure it shows after hiding (for re-submitting) */
		$field.focus();
		if( tip && $tip ){
			$tip.innerHTML = tip;
			$tip.setStyle('display', 'block');
			$($tip.parentNode).setStyle('height', 24);
			$field.addEvent('blur', function(event){
				this.removeEvent('blur', arguments.callee);	/* Avoid collecting events */
				$blur = new Fx.Tween('tip_'+field, {property:'opacity', duration:300, fps:50, onComplete:function(){
					this.set('display', 'none');
					this.set('opacity', 1);
					$($tip.parentNode).setStyle('height', '');
				}} ).start(0);
			} );
		};
		var name = ($('field_'+field)||{}).innerHTML || '';
		var msg = 'El valor del campo %s no es válido. Por favor verifique el dato ingresado';
		showStatus( sprintf(msg, name) );
	}, 300 );
};

Modal = {
	wins: [],
	curtain: 'curtain',
	open: function( obj ){
		if( !$(obj) ) return;
		for( var i=0, win ; win=this.wins[i] ; i++ ) win.removeClass('modalWin');
		this.wins.push( $(obj).addClass('modalWin') );
		if( !window.IN_FRAME ) $(this.curtain).setStyle('display', 'block');
	},
	close: function(){
		if( this.wins.length ) this.wins.pop().removeClass('modalWin');
		if( !this.wins.length ) $(this.curtain).setStyle('display', 'none');
		else this.wins[this.wins.length - 1].addClass('modalWin');
	}
};



/***********************************************************************************/
/*********************************** A G E N D A ***********************************/
/***********************************************************************************/

function setAgendaHandlers(){
	if( $$('.eventUnit') ) $$('.eventUnit').forEach(function(evt){
		evt.addEvent('click', function eventInfo(){
			xajax_eventInfo( $(this).getElement('INPUT').value );	/* eventID in hidden input */
		});
	});
};

function closeAgendaEvent( id ){
	var res = prompt('Escriba un comentario para el cierre de este evento:');
	if( !res && res !== null ) return showStatus('Se debe incluir un comentario al cerrar un evento de la Agenda.');
	if( res ) xajax_closeAgendaEvent(id, res||'', $('rescheduled').checked ? 1 : 0);
};


/***********************************************************************************/
/************************************ TABS ************************************/
/***********************************************************************************/

function initializeTabButtons(){
	if( $('tabButtons') ) $('tabButtons').getElements('DIV').forEach(function(tab){
		tab.removeEvents('click');
		tab.addEvent('click', function(e){
			e.stop();
			xajax_switchTab( this.getAttribute('FOR') );
		});
	});
};

/***********************************************************************************/
/************************************ L I S T S ************************************/
/***********************************************************************************/

function initializeList(code, modifier, src){
	// Make sure the list exists (listFrame, named {code}List)
	var $list = $('listWrapper'), $titles = $('tableTitles');
	if( !$list || !$titles ) return raise('missing required elements');
	if( !$list.update ) $list.update = function(){
		fixTableHeader($titles, $$('.listTable')[0]);
		$$('.listRows').forEach(function(row){
			row.addEvent('mouseover', function(){ highLight(this); });
			if( row.getAttribute('FOR') ){
				row.addEvent('click', function(e){
					getPage(e, code + 'Info', [row.getAttribute('FOR')]);
				});
			};
		});
		$$('.tblTools').forEach(function(tool){
			var axn = tool.getAttribute('AXN');
			var id = tool.getAttribute('FOR');
			tool.addEvent('click', function(e){
				e.stop();
				switch( axn ){
					case 'delete':
						if( confirm('¿Realmente desea eliminar este elemento?') ){
							window['xajax_delete' + code.capitalize()](id, modifier);
						};
						break;
					case 'block':
						if( confirm('¿Realmente desea bloquear este elemento?') ){
							window['xajax_block' + code.capitalize()](id, modifier);
						};
						break;
					default: 
						getPage(e, axn + code.capitalize(), [id, modifier]);
						break;
				};
			});
		});
	};
	TableSearch.enableSearch(code, modifier, src||'');		/* Prepare search tools */
};

function initializeSimpleList(code, modifier){
	var SimpleList = function( $list ){			// Simple List
		var that = this;
		var row4edit = $list.getElement('.addItemToSimpleList');
		this.inputs = row4edit.getElements('INPUT, SELECT');
		var editting = {};
		this.createItem = function(){
			var data = editting.id ? {SL_ID: editting.id} : {};
			that.inputs.forEach(function(input){ data[input.name] = input.value; });
			var func = 'xajax_create' + code.capitalize();
			if( window[func] ) window[func](data, modifier);
		};
		this.enableEditItem = function( id ){
			var tgt = that.selectRow( id );
			$('createItemText').innerHTML = 'Modificar';
			editting = {id: id, row: tgt};
		};
		this.selectRow = function( id ){
			that.disableEditItem();
			// Locate the row we selected in the DOM
			var i = 0, tgt;
			while( (tgt=$list.rows[i++]) && tgt.getAttribute('FOR') !== id );
			// Clone its cells' values into the input boxes below
			var j = 0;
			that.inputs.forEach(function(el){
				var val = tgt.cells[j].innerHTML;
				if( el.options ) selectOption(el, val, 'text');
				else el.value = tgt.cells[j].innerHTML;
				j++;
			});
			return tgt;
		};
		this.disableEditItem = function(){
			if( editting.tgt ) editting.tgt.removeClass('selectedRow');
			that.inputs.forEach(function(inp){ inp.value = ''; });
			$('createItemText').innerHTML = 'Agregar';
			editting = {};
		};
	};
	$$('.simpleList').forEach(function($list){
		var SL = new SimpleList( $list );
		SL.inputs.forEach(function(input){
			input.addEvent('enter', function(){ $('SLcreateItem').fireEvent('click'); });
		});
		$list.getElements('.listRows').forEach(function(row){
			row.addEvent('mouseover', function(){ highLight(this); });
			row.addEvent('click', function(){ SL.enableEditItem( this.getAttribute('FOR') ); });
		});
		$('createItemText').onclick = SL.createItem;
		$list.getElements('.tblTools').forEach(function(tool){
			var id = tool.getAttribute('FOR');
			var axn = tool.getAttribute('AXN');
			var func = 'xajax_' + axn + code.capitalize();
			tool.addEvent('click', function(e){
				if( e ) e.stop();
				switch( axn ){
					case 'create':
						return SL.createItem();
					case 'edit':
						return SL.enableEditItem( id );
					case 'delete':
						if( !confirm('¿Realmente desea eliminar este elemento?') ) return;
						break;
					case 'block':
						if( !confirm('¿Realmente desea bloquear este elemento?') ) return;
						break;
				};
				if( !window[func] ) throw('Function ' + func + ' is not registered!');
				window[func](id, modifier);
			});
		});
	});
};

function enableComboList(){
	$$('.comboList').forEach(function(cl){
		cl.addEvent('change', function(e){
			getPage(e, this.getAttribute('FOR') + 'Info', [this.value]);
		});
	});
};

var TableSearch = {
	/* Properties */
	Box: null,					/* Search box */
	Input: null,				/* Input field */
	CloseButton: null,			/* Button to close search tools */
	Buttons: [],				/* Collection of search buttons in the page */
	funcAtts: [],				/* Attributes to pass on through Xajax */
	showing: null,				/* Currently shown search box */
	searchID: null,				/* Unique ID for each search request */
	cacheBox: null,				/* DOM box to contain returned results */
	lastSearch: null,			/* Last searched term */
	/* Methods */
	enableSearch: function(){
		if( !this.ini() ) throw('missing TableSearch parameters');
		this.Buttons = $$('.tableColumnSearch');
		this.populateList( arguments );
		for( var i=0, att, btn ; btn=this.Buttons[i] ; i++ ){
			att = btn.getAttribute('FOR');
			btn.setAttribute('TableSearchCol', i);
			btn.addEvent('click', function(e){ TableSearch.present(e, this, att); } );
		};
		/* Do first search (unfiltered) */
		this.process( true );
	},
	ini: function(){
		var that = this;
		var boxes = $$('.TableSearchBoxes')||[];
		if( boxes.length > 1 ) document.body.removeChild(boxes[1]);
		this.Box = $('TableSearchBox');
		this.Input = $('TableSearchInput');
		this.CloseButton = $('TableSearchCloseButton');
		this.Input.addEvent('keyup', function(e){ that.process(e); });
		this.CloseButton.addEvent('click', function(){ that.hideBox.apply(that); });
		this.createCacheBox();
		this.showing = -1;
		return this.Box && this.Input && BODY.appendChild( this.Box );
	},
	createCacheBox: function(){
		if( $('TableSearchCache') ) return;
		var tmp = this.cacheBox = $(document.createElement('DIV')).setStyle('display', 'none');
		tmp.id = 'TableSearchCache';
		BODY.appendChild( tmp );
	},
	populateList: function( args ){
		this.funcAtts = [];
		for( var i=0, arg ; arg=args[i] ; i++ ) this.funcAtts.push( arg );
	},
	present: function(e, obj, att){
		if( this.showing == obj.getAttribute('TableSearchCol') ) return this.hideBox();
		this.hideBox();
		this.showBox(e, obj);
	},
	hideBox: function(){
		this.Box.style.display = 'none';
		this.Input.value = '';
		if( this.showing >= 0 ) this.process( true );
		this.showing = -1;
	},
	showBox: function(e, tgt){
		this.Box.setStyle('left', e.page.x - 100);
		this.Box.setStyle('top', e.page.y + 48);
		this.Box.setStyle('display', 'block');
		this.showing = tgt.getAttribute('TableSearchCol');
		this.Box.getElement('SPAN').innerHTML = this.Buttons[this.showing].alt;
		$('TableSearchInput').focus();
	},
	process: function( clear ){
		/* Don't repeat search when keyup provoked no changes */
		var searchString = this.Input.value.replace('*', '%');
		if( this.lastSearch == searchString ) return;
		/* Build filter */
		var filter = {};
		if( clear !== true ){
			var col = this.Buttons[this.showing].getAttribute('FOR');
			filter[col] = this.lastSearch = searchString;
		};
		/* Pass the input and aditional info to registered xajax function */
		var params = [this.searchID=newSID().toString()].concat(filter, this.funcAtts);
		xajax_updateList.apply(window, params);
	},
	showResults: function( uID ){
		/* Make sure we're receiving the most recent request */
		if( !$('listWrapper') || uID != this.searchID ) return;
		$('listWrapper').innerHTML = this.cacheBox.innerHTML;
		this.cacheBox.innerHTML = '';
	}
};


/*************************************************************************************************/
/************************************** O N D O M R E A D Y **************************************/
/*************************************************************************************************/

window.addEvent('domready', function(){			/* Global variables (pseudo constants) */
	BODY = document.getElementsByTagName('BODY')[0]||document.createElement('DIV');
	CONTENT = $('main_box');
} );

if( !window.IN_FRAME ){
	
	window.addEvent('domready', function(){			/* Main container's transition */
		window.addEvent('resize', function(){
			CONTENT.setStyle('height', $(BODY).getHeight() - 145 - (Browser.ie ? 10 : 0) + 'px');
			fixTableHeader();
		} );
		new Fx.Tween(
			'main_container',
			{property:'opacity', duration:200, fps:50, onStart: function(){
				this.set(0.5);
				window.fireEvent('resize');
			} }
		).start(0.5, 1);
	});
	
	window.addEvent('domready', function(){			/* Menu */
		($('hideMenu')||{}).onclick = hideMenu;
		($('showMenu')||{}).onclick = showMenu;
	});
	
};