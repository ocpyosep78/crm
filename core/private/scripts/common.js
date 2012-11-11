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

J(function($){
	window.BODY = $('body');
	window.CONTENT = $('#main_box');

	// Menu animation
	$('#hideMenu').click(hideMenu);
	$('#showMenu').click(showMenu);

	// Styled ToolTips
	$('body').qtip();

	// Activate Notifications (errors, success notifications, warnings)
	window.say = function(txt, type, stay) {
		var mt = txt.split(/<br ?\/>./, 3).length * 1.4;
		var cl = ($.isNumeric(type||0) ? (type ? 'success' : 'error') : type);
		$('#notifications').hide(1)._class(cl + 'Status')
			.find('div:last').html(txt).css('margin-top', -(mt/2) + 'em').end()
			.show('drop', {direction:'up'}, 500, function(){
				// Cancel pending hiding and queue a new one (0 == don't hide)
				clearTimeout(J(this).data('hto'));
				(stay !== 0) && J(this).data('hto', setTimeout(function(){
					$('#notifications').fadeOut({queue:false});
				}, (stay||10)*1000));
			});
	};
	$('#notifications').click(function(){
		J('#notifications').hide({duration:'fast', effect: 'blind'});
	});

	// Highlighting table rows and other elements
	$('body').on('mouseenter', '.highlight', function(){
		$(this).effect('highlight', {color: '#f0f0e6'}, 300);
	});

	// Activate highlighting of input fields
	var input_selector = '[type="text"].input, [type="password"].input';
	$('body').on('focus', input_selector, function(){
		$('.inputFocused').removeClass('inputFocused');
		$(this).addClass('inputFocused');
		$(this).select && $(this).select();
	}).on('blur', input_selector, function(){
		$(this).removeClass('inputFocused');
	});

	// Add handler to old comboList widget
	$('body').on('change', '.comboListOld', function(e){
		getPage(e, $(this)._for() + 'Info', [$(this).val()]);
	});

	// Tabs functionality
	$('body').on('click', '#tabButtons div', function(){
		return xajax_switchTab($(this)._for()) & false;
	});

	// FileForm: add pseudo-ajax submit to forms with File inputs
	$('body').one('submit', 'form:has(input[type="file"][ffcb])', function(){
		$('<iframe />', {id:'fffr', name:'fffr'}).hide().appendTo('body');
		var cb = $(this).find('input[type="file"][ffcb]').attr('ffcb');
		$(this).append($('<input type="hidden" name="ffcb" value="'+cb+'" />'))
			.attr({target:'fffr', method:'post', enctype:'multipart/form-data'})
			.submit(function(){ showLoading(); });
	});


	// Activate liquid table headers
	$(window).on('resize menutoggled', function(){
		$('#listTable').trigger('modified');
		J('.listWrapper').trigger('fill');
	});

	$('body').on('modified', '#listTable', function(){
		setTimeout(function(){
			var firstRow = $('#listTable tr');
			$('#tableTitles').toggle(!!firstRow.length)
				.find('div').each(function(i){
					var td = firstRow.find('td:eq('+i+')'),
						lastCol = !this.next('div').length;
					this.width((td && !lastCol) ? td.width()+1 : '').toggle(!!td);
				}, true);
		}, 300);
	});
});


/******************************************************************************/
/********************************* L O G I N **********************************/
/******************************************************************************/

// Login page only
J(function(){
	var frm = J.forms('formLogin');

	if (frm.length) {
		// Show the main logo above when in Login window
		say('', 'none', 0);

		frm.user.addClass('inputFocused').focus();

		frm.submit(function(){
			if (!frm.user.val()) {
				var msg = 'Debe escribir un nombre de usuario.';
				return frm.user.focus() & say(msg, 'error', 0) & false;
			} else if (!frm.pass.val()) {
				var msg = 'Debe escribir su contraseña.';
				return frm.pass.focus() & say(msg, 'error', 0) & false;
			} else {
				xajax_login(frm.user.val(), frm.pass.val());
			}

			return false;
		});
	}
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
	J('#main_box').animate({'margin-left': show ? 250 : 30}, 100);

	J('#showMenu').toggle(!show);
	J('#hideMenu').toggle(show);

	J(window).trigger('menutoggled');
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
		var that = this;
		var close = function(){
			window.parent.Frames.garbageCollect(window.frameElement._id());
		}
		J('body').escape(close);
		J('#FrameCloseBtn').click(close);
		J(window.parent).resize(function(){
			that.fixDimensions();
		});
		this.fixDimensions();
		window.frameElement.show();
	},
	loadPage: function( href ){
		var id = this.frames.length, d = document;
		var ifr = this.frames[id] = J('<iframe />').appendTo('body');
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
		$('notifications').setStyle('width', w + 10);
	},
	close: function(msg, code){
		msg && window.parent.say(msg, code||0);
		window.parent.Frames.garbageCollect(window.frameElement.uID);
	},
	garbageCollect: function(id) {
		J('iframe#'+id+', iframe[name="'+id+'"]').remove();
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

function FTshowTip(field, tip){
	var $field = J('#'+field),
	    $tip   = J('#tip_'+field),
	    $blur;

	if (!$tip) throw( 'No existe el elemento ' + field );

	setTimeout( function(){	/* Blur preceeds focus, so make sure it shows after hiding (for re-submitting) */
		$field.focus();
		if (tip && $tip) {
			$tip.html(tip).show().parent().height(24);
			$field.on('blur.ft', function(event){
				$(this).removeEvent('blur.ft');
				$blur = new Fx.Tween('tip_'+field, {property:'opacity', duration:300, fps:50, onComplete:function(){
					this.set('display', 'none');
					this.set('opacity', 1);
					$tip.parent().height('');
				}} ).start(0);
			});
		};

		var name = J('#field_'+field).html() || '',
		    msg = 'El valor del campo ' + name + ' no es válido. ' +
		          'Verifique el dato ingresado e inténtelo nuevamente.';
		say(msg);
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
        return say('Se debe incluir un comentario al ' + action + ' un evento.');
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
	J('#listWrapper').prop('update', function(){
		J('#listTable').trigger('modified');

		J('.listRows').click(function(e){
			J(this)._for() && getPage(e, code + 'Info', [J(this)._for()]);
		});

		J('.tblTools').click(function(e){
			var axn = J(this).attr('axn');
			var id = J(this)._for();

			switch (axn) {
				case 'delete':
					if( confirm('¿Realmente desea eliminar este elemento?') ){
						window['xajax_delete' + J.capitalize(code)](id, modifier);
					};
					break;
				case 'block':
					if( confirm('¿Realmente desea bloquear este elemento?') ){
						window['xajax_block' + J.capitalize(code)](id, modifier);
					};
					break;
				default:
					getPage(e, axn + J.capitalize(code), [id, modifier]);
					break;
			};

			return false;
		});
	});

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
			var func = 'xajax_create' + J.capitalize(code);
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

			var func = 'xajax_' + J(this).attr('axn') + J.capitalize(code);
			if( !window[func] ) throw('Function ' + func + ' is not registered!');

			return window[func](J(this)._for(), modifier) & false;
		});
	});
};

var TableSearch = {
	/* Properties */
	Box: null,					/* Search box */
	funcAtts: [],				/* Attributes to pass on through Xajax */
	showing: null,				/* Currently shown search box */
	searchID: null,				/* Unique ID for each search request */
	lastSearch: null,			/* Last searched term */
	/* Methods */
	enableSearch: function(){
		var that = this;
		// Remove all TS boxes but one
		J('.TSBoxes:gt(0)').remove();
		this.funcAtts = J.makeArray(arguments);
		this.Box = J('#TSBox').appendTo('body');

		if (!J('#TSCache').length) {
			J('<div />')._id('TSCache').hide().appendTo('body');
		}

		J('#TSInput').keyup(function(){
			that.process();
		});

		J('#TSCloseBtn').click(function(){
			that.hideBox.apply(that);
		});

		J('.tableColumnSearch').click(function(e){
			that.present(e, J(this), J(this)._for());
		}).attr('TSCol', function(i){ return i; });

		this.showing = -1;
		this.process(true); // Run first search, unfiltered
	},
	present: function(e, obj, att){
		if (this.showing == obj.attr('TSCol')) {
			return this.hideBox();
		}
		this.hideBox();
		this.showBox(e, obj);
	},
	hideBox: function(){
		this.Box.hide();
		J('#TSInput').val('');
		(this.showing >= 0) && this.process(true);
		this.showing = -1;
	},
	showBox: function(e, tgt){
		this.Box.attr({left: e.page.x - 100, top: e.page.y + 48}).show()
			.find('span').html(J('.tableColumnSearch')[this.showing]._alt());
		this.showing = tgt.attr('TSCol');
		J('#TSInput').focus();
	},
	process: function(clear){
		/* Don't repeat search when keyup provoked no changes */
		var q = J('#TSInput').val(J('#TSInput').val().replace('*', '%'));
		if (this.lastSearch == q) return;
		/* Build filter */
		var filter = {};
		if (!clear) {
			var col = J('.tableColumnSearch')[this.showing]._for();
			filter[col] = this.lastSearch = q;
		}
		/* Pass the input and aditional info to registered xajax function */
		this.searchID = newSID().toString();
		var params = [this.searchID].concat(filter, this.funcAtts);
		xajax_updateList.apply(window, params);
	},
	showResults: function(uID) {
		/* Make sure we're receiving the most recent request */
		if (!J('#listWrapper') || uID != this.searchID) return;
		J('#listWrapper').html(J('#TSCache').html());
		J('#TSCache').html('');
	}
};