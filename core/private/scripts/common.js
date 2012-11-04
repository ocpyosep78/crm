/******************************************************************************/
/******************************** J Q U E R Y *********************************/
/******************************************************************************/

J = jQuery.noConflict();

// Localize DatePicker
jQuery.datepicker.setDefaults(jQuery.datepicker.regional['es'] = {
	closeText: 'Cerrar',
	prevText: '&#x3c;Ant',
	nextText: 'Sig&#x3e;',
	currentText: 'Hoy',
	monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio', 'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
	monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun', 'Jul','Ago','Sep','Oct','Nov','Dic'],
	dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
	dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
	dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
	weekHeader: 'Sm',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
});

// Make $().each to provide extended arguments and scope, when args === true
jQuery.fn.each = function(cb, args) {
	if (!jQuery.isArray(args) && args) {
		var jSet = this;
		args = null; // We don't want to pass a truthy args to jQuery.each()
		jQuery.each(this, function(i, k){ jSet[i] = jQuery(k); });
	}

	return jQuery.each(this, cb, args);
};

// Implement custom onEnter and onEscape jQuery events
jQuery.fn.enter = function(cb){
	return cb ? this.on('enter', cb) : this.trigger('enter');
}
jQuery.fn.escape = function(cb){
	return cb ? this.on('escape', cb) : this.trigger('escape');
}
jQuery(function($){
	$('body').on('keyup', '*', function(e){
		return (e.which != 13) || $(this).enter();
	});
	$('body').on('keyup', ':input', function(e){
		return (e.which != 27) || $(this).escape();
	});
});

// Extend forms, add entries for its elements, and add names when requested
jQuery.forms = function(form, addNames) {
	var frm = jQuery('form#' + form + ', form[name="' + form + '"]').first();

	// Add the names, based on the ids (with an optional prefix)
	if (addNames) {
		var prefix = (typeof addNames === 'string') ? addNames : '';
		frm.find(':input:not([name])[id^="' + prefix + '"]').each(function(){
			var name =  jQuery(this).attr('id').substr(prefix.length);
			jQuery(this).attr('name', name);
		});
	}

	frm.elements = frm.find(':input[name]').each(function(i){
		var name = jQuery(this).attr('name');
		frm[name] = frm.find(':input[name="' + name + '"]');
	});

	// Add reset method (which doesn't exist in jQuery for some reason)
	frm.reset = function() {
		frm.get(0) && frm.get(0).reset();
		return this;
	}

	return frm;
};

// Add the most relevant attributes as direct jQuery objects methods
jQuery.map(['id', 'name', 'class', 'src', 'type', 'for', 'rel'], function(attr){
	jQuery.fn['_'+attr] = function(val){
		return this.attr.apply(this, jQuery.merge([attr], arguments));
	};
});

// Add method print to jQuery objects
jQuery.fn.print = function() {
	this.get(0) && this.get(0).print();
	return this;
}


/******************************************************************************/
/***************************** D E B U G G I N G ******************************/
/******************************************************************************/

function raise( msg ){
	var caller = (arguments.callee.caller && arguments.callee.caller.name)
		? arguments.callee.caller.name + ' '
		: '';
	return DEVELOPER_MODE ? !!alert( caller + 'error: ' + (msg||'') ) : false;
};


/******************************************************************************/
/******************************* D O M R E A D Y ******************************/
/******************************************************************************/

J(function(){
	window.BODY = J('body');
	window.CONTENT = J('#main_box');

	/* Main container's transition */
	window.IN_FRAME || J(window).resize(function(){
		fixTableHeader();
	}).resize();

	// Menu animation
	J('#hideMenu').click(hideMenu);
	J('#showMenu').click(showMenu);
	J(window).on('menutoggled', fixTableHeader);
	
	// Hide status messages when clicking on them
	J('#statusMsgs').click(function(){ hideStatus(); });
	
	// Highlighting table rows and other elements
	J('body').on('mouseenter', '.highlight', function(){
		J(this).effect('highlight', {color: '#f0f0e6'}, 300);
	});

	// Activate highlighting of input fields
	var input_selector = '[type="text"].input, [type="password"].input';
	J('body').on('focus', input_selector, function(){
		J(this).removeClass('input').addClass('inputFocused');
		J(this).select && J(this).select();
	});
	J('body').on('blur', input_selector, function(){
		J(this).removeClass('inputFocused').addClass('input');
	});

	// Tabs functionality
	J('body').on('click', '#tabButtons div', function(){
		return xajax_switchTab(J(this)._for()) & false;
	});
});


/******************************************************************************/
/**************************** N A V I G A T I O N *****************************/
/******************************************************************************/

function getPage() {
	var args = Array.from( arguments );
	var ctrl = (typeof(args[0]) == 'object') ? (args[0].ctrlKey|args.shift().control) : false;
	return (ctrl ? xajax_showPage : xajax_getPage).apply(null, args);
}

// Calls a page function to update content
function loadContent(){ xajax_loadContent() };

// Loads a page within an iframe
function showPage(){ xajax_showPage() };

// Initialize loaded page and events associated to new elements in it
function iniPage(name) {
	try{
		var fn = window['ini_'+name];
		fn && fn.apply(fn, IniParams.get());

		// Add handler to old comboList widget
		applyOldComboListHandlers();

		// Add links to objects signled by model and id (hidden in the markup)
		applyLink2Model();

		// Style browse buttons
		applyBrowseButtonsStyle();
	} catch(e) {
		return DEVELOPER_MODE ? test(e) : false;
	}
};

// Current page's parameters (persistence)
var IniParams = {
	params: null,
	set: function( data ){
		this.params = Array.from(data);
	},
	get: function(){
		var params = Array.from(this.params);
		delete(this.params);
		return params||{};
	}
};

// Add handler to old comboList widget
function applyOldComboListHandlers() {
	J('.comboListOld').change(function(e){
		getPage(e, J(this)._for() + 'Info', [J(this).val()]);
	});
}

// Add links to objects signled by model and id (hidden in the markup)
function applyLink2Model() {
	CONTENT.find('.link2model').each(function(i, lnk){
		var atts = (lnk._for()||'').split('|');
		atts[0] && lnk.click(function(e){
			getPage(e, atts[0], [atts[1]]);
		});
	}, true);
}

// Style browse buttons
function applyBrowseButtonsStyle() {
	var browse = J('<div />', {'class': 'browse_box'})
		.append(J('<input type="text" />')
			.attr({'class': 'browse_txt', 'disabled': true}))
		.append(J('<input type="button" value="Examinar..." />')
			.attr({'class': 'browse_btn'}))
		.append(J('<div />')
			.attr({'class': 'browse_hdn'}));
	
	J(':file').each(function(i, btn){
		// Put the box next to the real button, then embed this button inside
		browse.clone(true).insertBefore(btn).find('.browse_hdn').append(btn);

		btn.change(function(){
			var file = btn.val().split(/[\\\/]/).pop();
			btn.parents('browse_box').find('.browse_txt').val(file);
		})
	}, true);
}

function hideMenu() {
	toggleMenu(false);
}

function showMenu() {
	toggleMenu(true);
}

function toggleMenu(show) {
	J('#menuDiv')[show ? 'show' : 'hide']('drop', {}, show ? 100 : 200);
	J('#main_box').animate({'margin-left': show ? 220 : 30}, 100);

	J('#showMenu').toggle(!show);
	J('#hideMenu').toggle(show);
}

function flagMenuItem(code) {
	J('.menuItem.currentPage').removeClass('currentPage');
	J('.menuItem[for="' + code + '"]').addClass('currentPage');
}

function switchNav(e, obj){
	if( e && (e.control||e.ctrlKey) ) return;
	for( var i=0, navs=J('.navCurrMod'), nav ; nav=navs[i] ; i++ ) nav.className = 'navMod';
	$(obj).className = 'navCurrMod';
};


/******************************************************************************/
/******************************** F R A M E S *********************************/
/******************************************************************************/

J(function(){ IN_FRAME && Frames.initialize(); });

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
		msg && window.parent.showStatus(msg, code||0);
		window.parent.Frames.garbageCollect(window.frameElement.uID);
	},
	garbageCollect: function(id) {
		J('iframe#'+id+', iframe[name="'+id+'"]').detach();
	}
}


/******************************************************************************/
/******************************* G E N E R A L ********************************/
/******************************************************************************/

function in_array( needle, hStack ){    /* Emulate PHP's in_array for both arrays and objects */
	if( typeof(hStack) == 'object' ) for( var i in hStack ) if( hStack[i] == needle ) return true;
}

function silentXajax(func, params){
	var currentLoadingFunction = xajax.loadingFunction;
	xajax.loadingFunction = function(){};
	xajaxWaitCursor = false;
	(window['xajax_'+func]||function(){}).apply(window, params||[]);
	xajax.loadingFunction = currentLoadingFunction;
	xajaxWaitCursor = true;
}


/******************************************************************************/
/****************************** S H O R T C U T S *****************************/
/******************************************************************************/

function getOption(oCombo, sVal, sBy){	// sBy: 'value' (default) or 'text'
	for( var i=0, opt ; opt=oCombo.options[i] ; i++ ) if( opt[(sBy||'value')] == sVal ) return i;
	return null;
};
function selectOption(oCombo, sVal, sBy){	// sBy: 'index' (default), 'value' or 'text'
	if( !oCombo.get('multiple') ){
		for( var i=0, opt ; opt=oCombo.options[i] ; i++ ) opt.removeAttribute('selected');
	};
	if( sBy && sBy != 'index' ) selectOption(oCombo, getOption(oCombo, sVal, sBy));
	else if( sVal >= 0 && sVal < oCombo.options.length ) oCombo.options[sVal].selected = true;
};

function xajaxSubmit(oForm, sFunc, showDisabled, atts){		/* Submit form through ajax */
	window['xajax_'+sFunc]( xajax.getFormValues(oForm, showDisabled), atts||[] );
}

function newTip(id, obj){
	var tip = document.createElement('DIV');
	tip.id = 'tip_' + id;
	if( obj ){
		$(obj.parentNode).addClass('Tip');
		obj.parentNode.insertBefore(tip, obj.nextSibling);
	}
	return tip;
}

function showTip(field, tip){
	clearTimeout( showTip.to );
	(showTip.sel||{}).html('');
	if( !($field=$('tip_' + field)) ) throw('No existe el tip ' + field);
	$field.html(tip);
	showTip.to = setTimeout(function(){ $field.html(''); }, 5000);
	showTip.sel = $field;
}

function FTshowTip( field, tip ){
	var $field = $(field), $tip = $('tip_'+field), $blur;
	if( !$tip ) throw( 'No existe el elemento ' + field );
	setTimeout( function(){	/* Blur preceeds focus, so make sure it shows after hiding (for re-submitting) */
		$field.focus();
		if( tip && $tip ){
			$tip.html(tip);
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

		var name = J('#field_'+field).html() || '';
		var msg = 'El valor del campo ' + name + ' no es válido.' +
		          'Por favor verifique el dato ingresado';
		showStatus(msg);
	}, 300 );
}

Modal = {
	wins: [],
	curtain: 'curtain',
	open: function( obj ){
		if( !$(obj) ) return;
		var newObj = $(obj).clone().addClass('modalWin').set('id', '');
		this.wins.forEach(function(win){ win.setStyle('display', 'none'); });
		this.wins.push( newObj.inject(document.body) );
		if( !window.IN_FRAME ) $(this.curtain).setStyle('display', 'block');
	},
	close: function(){
		if( this.wins.length ) this.wins.pop().destroy();
		if( !this.wins.length ) $(this.curtain).setStyle('display', 'none');
		else this.wins[this.wins.length - 1].setStyle('display', 'block');
	}
};



/***********************************************************************************/
/*********************************** A G E N D A ***********************************/
/***********************************************************************************/

function setAgendaHandlers(){
	J('.eventUnit').click(function(){
		xajax_eventInfo(J(this).find('input[type="hidden"]').val());
	});
};

function closeAgendaEvent(id, msg, resched){
	(typeof resched === 'undefined') && (resched = J('#showRescheduled').attr('checked'));

	var action = resched ? 'cancelar' : 'cerrar';
	var res = prompt('Escriba un comentario para ' + action + ' el evento:', msg||'');
	
	if( res === '' ){
        return showStatus('Se debe incluir un comentario al ' + action + ' un evento.');
    }else if( res ){
        var cfm = "Se dispone a " + action + " el evento con el siguiente mensaje:\n\n" + res +
                  "\n\nPulse Cancelar para editar el mensaje o Aceptar para confirmar.";
        if (confirm(cfm)){
            xajax_closeAgendaEvent(id, res, resched ? 1 : 0);
        } else {
            closeAgendaEvent(id, res, resched);
        }
    }
};


/***********************************************************************************/
/************************************ L I S T S ************************************/
/***********************************************************************************/

function initializeList(code, modifier, src){
	// Make sure the list exists (listFrame, named {code}List)
	var $list = $('listWrapper'), $titles = $('tableTitles');
	if( !$list || !$titles ) return raise('missing required elements');
	if( !$list.update ) $list.update = function(){
		fixTableHeader($titles, J('.listTable')[0]);
		J('.listRows').click(function(e){
			J(this)._for() && getPage(e, code + 'Info', [J(this)._for()]);
		});
		J('.tblTools').click(function(e){
			var axn = J(this).attr('axn');
			var id = J(this)._for();

			switch (axn) {
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

			return false;
		});
	};

	TableSearch.enableSearch(code, modifier, src||'');		/* Prepare search tools */
};

function initializeSimpleList(){
	var SimpleList = function($list, code, modifier){			// Simple List
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
			$list.getElement('.createItemText').html('Modificar');
			editting = {id: id, row: tgt};
		};
		this.selectRow = function( id ){
			that.disableEditItem();
			// Locate the row we selected in the DOM
			var i = 0, tgt;
			while( (tgt=$list.rows[i++]) && tgt._for() !== id );
			// Clone its cells' values into the input boxes below
			var j = 0;
			that.inputs.forEach(function(el){
				var val = tgt.cells[j].html();
				if( el.options ) selectOption(el, val, 'text');
				else el.value = tgt.cells[j].html();
				j++;
			});
			return tgt;
		};
		this.disableEditItem = function(){
			if( editting.tgt ) editting.tgt.removeClass('selectedRow');
			that.inputs.forEach(function(inp){ inp.value = ''; });
			$list.find('.createItemText').html('Agregar');
			editting = {};
		};
	};
	J('.simpleList').each(function(i, $list){
        // Nothing to do if this list has no tools (no editting allowed)
        if (!$list.find('.addItemToSimpleList')) return false;
        var params = $list._for().split('|');
        var code = params[0], modifier = params[1];
		var SL = new SimpleList($list, code, modifier);
		SL.inputs.forEach(function(input){
			input.addEvent('enter', function(){
				$list.find('.SLcreateItem').click();
			});
		});
		$list.find('.listRows').click(function(){ SL.enableEditItem( this._for() ); });
		$list.find('.createItemText').click(SL.createItem);
		$list.find('.tblTools').click(function(e){
			switch (J(this).attr('axn')) {
				case 'create': return SL.createItem();
				case 'edit': return SL.enableEditItem(J(this)._for());
				case 'delete':
					if( !confirm('¿Realmente desea eliminar este elemento?') ) return;
					break;
				case 'block':
					if( !confirm('¿Realmente desea bloquear este elemento?') ) return;
					break;
			};

			var func = 'xajax_' + J(this).attr('axn') + code.capitalize();
			if( !window[func] ) throw('Function ' + func + ' is not registered!');

			return window[func](J(this)._for(), modifier) & false;
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
		this.Buttons = J('.tableColumnSearch');
		this.populateList( arguments );
		for( var i=0, att, btn ; btn=this.Buttons[i] ; i++ ){
			att = btn._for();
			btn.setAttribute('TableSearchCol', i);
			btn.addEvent('click', function(e){ TableSearch.present(e, this, att); } );
		};
		/* Do first search (unfiltered) */
		this.process( true );
	},
	ini: function(){
		var that = this;
		var boxes = J('.TableSearchBoxes')||[];
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
		if( this.showing == obj.attr('TableSearchCol') ) return this.hideBox();
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
		this.showing = tgt.attr('TableSearchCol');
		this.Box.find('span').html(this.Buttons[this.showing].alt);
		$('TableSearchInput').focus();
	},
	process: function( clear ){
		/* Don't repeat search when keyup provoked no changes */
		var searchString = this.Input.value.replace('*', '%');
		if( this.lastSearch == searchString ) return;
		/* Build filter */
		var filter = {};
		if( clear !== true ){
			var col = this.Buttons[this.showing]._for();
			filter[col] = this.lastSearch = searchString;
		};
		/* Pass the input and aditional info to registered xajax function */
		var params = [this.searchID=newSID().toString()].concat(filter, this.funcAtts);
		xajax_updateList.apply(window, params);
	},
	showResults: function( uID ){
		/* Make sure we're receiving the most recent request */
		if( !$('listWrapper') || uID != this.searchID ) return;
		$('listWrapper').html(this.cacheBox.html());
		this.cacheBox.html('');
	}
};

/* Fix Table Titles */
function fixTableHeader(oTitlesBox, oTable, cached){
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