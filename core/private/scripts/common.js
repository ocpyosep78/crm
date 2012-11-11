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
/********************************* T O O L S **********************************/
/******************************************************************************/

// Notifications
function say(txt, type, stay) {
	var mt = txt.split(/<br ?\/>./, 3).length * 1.4;
	var cl = (J.isNumeric(type||0) ? (type ? 'success' : 'error') : type);
	J('#notifications').hide(1)._class(cl + 'Status')
		.find('div:last').html(txt).css('margin-top', -(mt/2) + 'em').end()
		.show('drop', {direction:'up'}, 500, function(){
			// Cancel pending hiding and queue a new one (0 == don't hide)
			clearTimeout(J(this).data('hto'));
			(stay !== 0) && J(this).data('hto', setTimeout(function(){
				J('#notifications').fadeOut({queue:false});
			}, (stay||10)*1000));
		});
}

// Ajax without loading graphics
function silentXajax(func, params) {
	var fn = window['xajax_'+func],
	    loadFn = xajax.loadingFunction;
	xajax.loadingFunction = J.noop();
	xajaxWaitCursor = false;
	fn && fn.apply(window, params||[]);
	xajaxWaitCursor = true;
	xajax.loadingFunction = loadFn;
}

// Loading animation
function showLoading(show){
	J('#loadingGif').toggle(show !== false);
};

// Submit forms through ajax
function xajaxSubmit(oForm, sFunc, showDisabled, atts){
	window['xajax_'+sFunc]( xajax.getFormValues(oForm, showDisabled), atts||[] );
}

function importElement(id) {
	J('#'+id).append(J('#importedElement').contents());
}

// Show styled ToolTips (qTip2) near an element
function showTip(field, tip, position, area){
	field.qtip({content: {text: tip},
			   position: {at: position||'bottom left'},
			   solo: area||J('body'),
			   show: {target: J()}}).qtip('show');
}

// Show/Hide main menu
function toggleMenu(show) {
	J('#menuDiv')[show ? 'show' : 'hide']('drop', {}, show ? 100 : 200);
	J('#main_box').animate({'margin-left': show ? 250 : 30}, 100);

	J('#showMenu').toggle(!show);
	J('#hideMenu').toggle(show);

	J(window).trigger('menutoggled');
}

// Shortcuts to toggleMenu
function showMenu(){ toggleMenu(true);  }
function hideMenu(){ toggleMenu(false); }


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
	var fn = window['ini_'+name];

	try {
		fn && fn.apply(fn, IniParams.get());
	} catch(e) {
		return DEVELOPER_MODE ? test(e) : false;
	}

	J('body').trigger('contentload');
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

function flagMenuItem(code) {
	J('.menuItem.currentPage').removeClass('currentPage');
	J('.menuItem[for="' + code + '"]').addClass('currentPage');
}

function switchNav(e, obj){
	if (!e || !e.ctrlKey) {
		J('.navCurrMod')._class('navMod');
		J(obj)._class('navCurrMod');
	}
}


/******************************************************************************/
/******************************* D O M R E A D Y ******************************/
/******************************************************************************/

J(function($){
	// Menu animation
	$('#hideMenu').click(hideMenu);
	$('#showMenu').click(showMenu);

	$('#notifications').click(function(){
		J('#notifications').hide({duration:'fast', effect: 'blind'});
	});

	// Activate Debugger
	$('body').on('click', '#debugStats', function(){
		$('#debugger').toggle().filter(':visible')._src('index.php?stats');
	});

	// Activate highlighting of input fields
	$('body').on('focus',':input.input', function(){
		$('.inputFocused').removeClass('inputFocused');
		$(this).addClass('inputFocused').select();
	}).on('blur', ':input.input', function(){
		$(this).removeClass('inputFocused');
	});

	// Activate Loading widget on pageclose/navigation/ajax
	J(window).on('beforeunload', function(){ showLoading(); });
	xajax.loadingFunction = function(){ showLoading(); };
	xajax.doneLoadingFunction = function(){ showLoading(false); };
	xajax.loadingFailedFunction = function(){ showLoading(false); };

	// Tabs functionality
	$('body').on('click', '#tabButtons div', function(){
		return xajax_switchTab($(this)._for()) & false;
	});

	// Styled ToolTips
	$('body').qtip();

	// Activate link2model
	J('body').on('click', '.link2model', function(e){
		var atts = (J(this)._for()||'').split('|');
		atts[0] && getPage(e, atts[0], [atts[1]]);
		return false;
	});

	// Highlighting table rows and other elements
	$('body').on('mouseenter', '.highlight', function(){
		$(this).effect('highlight', {color: '#f0f0e6'}, 300);
	});

	// Add handler to old comboList widget
	$('body').on('change', '.comboListOld', function(e){
		getPage(e, $(this)._for() + 'Info', [$(this).val()]);
	});

	// Activate agenda eventUnits
	J('body').on('click', '.eventUnit', function(){
		xajax_eventInfo(J(this).find('input[type="hidden"]').val());
	});

	// FileForm: add pseudo-ajax submit to forms with File inputs
	$('body').one('submit', 'form:has(input[type="file"][ffcb])', function(){
		$('<iframe />', {id:'fffr', name:'fffr'}).hide().appendTo('body');
		var cb = $(this).find('input[type="file"][ffcb]').attr('ffcb');
		$(this).append($('<input type="hidden" name="ffcb" value="'+cb+'" />'))
			.attr({target:'fffr', method:'post', enctype:'multipart/form-data'})
			.submit(showLoading);
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

	// Actions on content load
	J('body').on('contentload', function(){
		// Apply styles to browse buttons (input type=file)
		var browse = J('<div />', {'class': 'browse_box'})
			.append(J('<input type="text" />')
				.attr({'class': 'browse_txt', 'disabled': true}))
			.append(J('<input type="button" value="Examinar..." />')
				.attr({'class': 'browse_btn'}))
			.append(J('<div />')
				.attr({'class': 'browse_hdn'}));

		J(':file:not(.file_styled)').each(function(i, btn){
			// Put the box next to the real button, then embed this button inside
			browse.clone(true).insertBefore(btn)
				.find('.browse_hdn').append(btn);
			// Flag as styled and sync file name between real and styled inputs
			btn.addClass('file_styled').change(function(){
				var file = btn.val().split(/[\\\/]/).pop();
				btn.parents('browse_box').find('.browse_txt').val(file);
			});
		}, true);
	});
});


/******************************************************************************/
/********************************* L O G I N **********************************/
/******************************************************************************/

// Login page only
J(function($){
	var frm = $.forms('formLogin');

	if (frm.length) {
		frm.user.addClass('inputFocused').focus();

		// Show the main logo above when login (if not visible already)
		setTimeout(function(){
			$('#notifications:hidden').length && say('', 'none', 0);
		}, 1000);

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
		var h = Math.round(J(window.frameElement).height() * 0.8);
		var w = Math.round(J(window.frameElement).width() * 0.9);
		J('frameArea').height(h).width(w);
		J('frameTitle').width(w);
		J('frameContent').css('marginTop', h/8 - 30);
		J('notifications').css('width', w + 10);
	},
	close: function(msg, code){
		msg && parent.say(msg, code||0);
		parent.Frames.garbageCollect(window.frameElement.uID);
	},
	garbageCollect: function(id) {
		J('iframe#'+id+', iframe[name="'+id+'"]').remove();
	}
}


/******************************************************************************/
/****************************** S H O R T C U T S *****************************/
/******************************************************************************/

Modal = {
	wins: [],
	curtain: 'curtain',

	open: function(win){
		if (win) {
			this.wins.hide(1);
			var newWin = win.clone()._class('modalWin')._id('').appendTo('body');
			this.wins.push(newWin);
			IN_FRAME || J('#'+this.curtain).show();
		}
	},

	close: function(){
		this.wins.length && this.wins.pop().destroy();
		if (this.wins.length) {
			J('#'+this.curtain).hide();
		} else {
			this.wins[this.wins.length-1].show();
		}
	}
};


/***********************************************************************************/
/*********************************** A G E N D A ***********************************/
/***********************************************************************************/

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
};

function initializeSimpleList(){
	function SimpleList(list, code, modifier){     // Simple List Constructor
		var my = function(sel) {
			return list.find(sel);
		}
		this.inputs = my('.addItemToSimpleList :input');

		this.createItem = function() {
			var data = {SL_ID: my('.selectedRow')._id()||''};
			this.inputs.each(function(){
				data[this._name()] = this.val();
			}, true);
			var func = 'xajax_create' + J.capitalize(code);
			window[func] && window[func](data, modifier);
		};

		this.enableEditItem = function(id) {
			this.disableEditItem();
			my('[for="'+id+'"]').addClass('selectedRow');
			my('.createItemText').html('Modificar');
			this.inputs.each(function(i){
				this.val(my('[for="'+id+'"] :input:eq('+i+')').html());
			}, true);
		};

		this.disableEditItem = function(){
			my('.selectedRow').removeClass('selectedRow');
			my('.createItemText').html('Agregar');
			this.inputs.val('');
		};
	};

	J('.simpleList').each(function(){
        if (!this.find('.addItemToSimpleList')) {
			return false;
		}
        var params = this._for().split('|'),
		    code = params[0],
		    modifier = params[1],
		    SL = new SimpleList(this, code, modifier);

		SL.inputs.enter(function(){
			this.find('.SLcreateItem').click();
		});

		this.find('.listRows').click(function(){
			SL.enableEditItem(this._for());
		});

		this.find('.createItemText').click(function(){
			SL.createItem();
		});

		this.find('.tblTools').click(function(e){
			switch (J(this).attr('axn')) {
				case 'create':
					return SL.createItem();
				case 'edit':
					return SL.enableEditItem(J(this)._for());
				case 'delete':
					if( !confirm('¿Realmente desea eliminar este elemento?') ) {
						return;
					}
					break;
				case 'block':
					if( !confirm('¿Realmente desea bloquear este elemento?') ) {
						return;
					}
					break;
			};

			var func = 'xajax_' + J(this).attr('axn') + J.capitalize(code);
			if( !window[func] ) {
				throw('Function ' + func + ' is not registered!');
			}

			return window[func](J(this)._for(), modifier) & false;
		});
	});
};