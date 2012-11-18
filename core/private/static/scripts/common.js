/******************************************************************************/
/******************************** J Q U E R Y *********************************/
/******************************************************************************/

// Localize DatePicker
$.datepicker.setDefaults($.datepicker.regional['es'] = {
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

// Extend $().each inner arguments and scope, when args == true (not array)
$.fn.each = function(cb, args) {
	if (!$.isArray(args) && args) {
		var jSet = this;
		args = null; // We don't want to pass a truthy args to $.each()
		$.each(this, function(i, k){ jSet[i] = $(k); });
	}

	return $.each(this, cb, args);
};

// Implement custom onEnter and onEscape $ events
$.fn.enter = function(cb){
	return cb ? this.on('enter', cb) : this.trigger('enter');
}
$.fn.escape = function(cb){
	return cb ? this.on('escape', cb) : this.trigger('escape');
}
$(function(){
	$('body').on('keyup', '*', function(e){
		return (e.which != 13) || $(this).enter();
	});
	$('body').on('keyup', ':input', function(e){
		return (e.which != 27) || $(this).escape();
	});
});

// Add serializeJSON (thanks Arjen Oosterkamp)
$.fn.serializeJSON = function() {
	var json = {};
	$.map(this.serializeArray(), function(n){
		json[n.name] = n.value;
	});
	return json;
}

// Extend forms, add entries for its elements, and add names when requested
$.forms = function(form, addNames) {
	var frm = $('form#' + form + ', form[name="' + form + '"]').first();

	// Add the names, based on the ids (with an optional prefix)
	if (addNames) {
		var prefix = (typeof addNames === 'string') ? addNames : '';
		frm.find(':input:not([name])[id^="' + prefix + '"]').each(function(){
			var name =  $(this).attr('id').substr(prefix.length);
			$(this).attr('name', name);
		});
	}

	frm.elements = frm.find(':input[name]').each(function(i){
		var name = $(this).attr('name');
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
var jDirectMethods = ['id', 'name', 'class', 'for', 'rel', 'title', 'checked',
                      'src', 'alt', 'type', 'target', 'method'];
$.map(jDirectMethods, function(attr){
	$.fn['_'+attr] = function(val){
		return this.attr.apply(this, $.merge([attr], arguments));
	};
});

// Add method print to jQuery objects
$.fn.print = function() {
	this.get(0) && this.get(0).print();
	return this;
}

$.capitalize = function(txt) {
	return txt.replace(/\b[a-z]/g, function(x){ return x.toUpperCase(); });
}


/******************************************************************************/
/**************************** P R O T O T Y P E S *****************************/
/******************************************************************************/
String.prototype.fill = function(i , s , r){	/* times, fillStr, reverse */
	if( i<0 ){ r = true; i = Math.abs(i); };
	if( i > this.length ) var a=(new Array(i-this.length+1)).join( s||0 );
	else return ( r ) ? this.substr(this.length-i--): this.substr( 0 , i );
	return r ? a+this.toString() : this.toString()+a;
};

function test(x) {
	console.log(x);
	alert('Debug log generated for: ' + x);
}

/******************************************************************************/
/***************************** D E B U G G I N G ******************************/
/******************************************************************************/

function raise( msg ){
	var caller = (arguments.callee.caller && arguments.callee.caller.name)
		? arguments.callee.caller.name + ' '
		: '';
	return DEVMODE ? !!alert( caller + 'error: ' + (msg||'') ) : false;
};


/******************************************************************************/
/********************************* T O O L S **********************************/
/******************************************************************************/

// Notifications
function say(txt, type, stay) {
	var mt = txt.split(/<br ?\/>./, 3).length * 1.4;
	var cl = ($.isNumeric(type||0) ? (type ? 'success' : 'error') : type);
	$('#notifications').hide(1)._class(cl + 'Status')
		.find('div:last').html(txt).css('margin-top', -(mt/2) + 'em').end()
		.show('drop', {direction:'up'}, 500, function(){
			// Cancel pending hiding and queue a new one (0 == don't hide)
			clearTimeout($(this).data('hto'));
			(stay !== 0) && $(this).data('hto', setTimeout(function(){
				$('#notifications').fadeOut({queue:false});
			}, (stay||10)*1000));
		});
}

// Ajax without loading graphics
function silentXajax(func, params) {
	var fn = window['xajax_'+func],
	    loadFn = xajax.loadingFunction;
	xajax.loadingFunction = $.noop();
	xajaxWaitCursor = false;
	fn && fn.apply(window, params||[]);
	xajaxWaitCursor = true;
	xajax.loadingFunction = loadFn;
}

// Loading animation
function showLoading(show){
	$('#loadingGif').toggle(show !== false);
};

// Submit forms through ajax
function xajaxSubmit(oForm, sFunc, showDisabled, atts){
	window['xajax_'+sFunc]( xajax.getFormValues(oForm, showDisabled), atts||[] );
}

function importElement(id, hide) {
	if (!$(id).length) {
		$('<div />').hide()._id(id.replace(/^#/, '')).appendTo('body');
	}

	$(id).toggle(!hide).append($('#importedElement').contents());
}

// Show styled ToolTips (qTip2) near an element
function showTip(field, tip, position, area){
	field.qtip({content: {text: tip},
			   position: {at: position||'bottom left'},
			   solo: area||$('body'),
			   show: {target: $()}}).qtip('show');
}

// Show/Hide main menu
function toggleMenu(show) {
	$('#menuDiv')[show ? 'show' : 'hide']('drop', {}, show ? 100 : 200);
	$('#main_box').animate({'margin-left': show ? 250 : 30}, 100);

	$('#showMenu').toggle(!show);
	$('#hideMenu').toggle(show);

	$(window).trigger('menutoggled');
}

// Shortcuts to toggleMenu
function showMenu(){ toggleMenu(true);  }
function hideMenu(){ toggleMenu(false); }


/******************************************************************************/
/**************************** N A V I G A T I O N *****************************/
/******************************************************************************/

function getPage() {
	var args = $.makeArray(arguments);
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
		return DEVMODE ? test(e) : false;
	}

	$('body').trigger('contentload');
}

// Current page's parameters (persistence)
var IniParams = {
	params: null,
	set: function( data ){
		this.params = $.makeArray(data);
	},
	get: function(){
		var params = $.makeArray(this.params);
		delete(this.params);
		return params||{};
	}
}

function flagMenuItem(code) {
	$('.menuItem.currentPage').removeClass('currentPage');
	$('.menuItem[for="' + code + '"]').addClass('currentPage');
}

function switchNav(e, obj){
	if (!e || !e.ctrlKey) {
		$('.navCurrMod')._class('navMod');
		$(obj)._class('navCurrMod');
	}
}


/******************************************************************************/
/******************************* D O M R E A D Y ******************************/
/******************************************************************************/

$(function(){
	// Menu animation
	$('#hideMenu').click(hideMenu);
	$('#showMenu').click(showMenu);

	// Activate closing notifications on click
	$('#notifications').click(function(){
		$('#notifications').hide({duration:'fast', effect: 'blind'});
	});

	// Activate Debugger
	$('body').on('click', '#debugStats', function(){
		$('#debugger').toggle().filter(':visible')._src('index.php?stats');
	}).on('click', '#openDebug', function(){
		$('#debugHeader').removeClass('dbgHid');
	}).on('click', '#debuggerbox', function(){
		$('#debugHeader').addClass('dbgHid');
	});

	// Activate highlighting of input fields
	$('body').on('focus',':input.input', function(){
		$('.inputFocused').removeClass('inputFocused');
		$(this).addClass('inputFocused').select();
	}).on('blur', ':input.input', function(){
		$(this).removeClass('inputFocused');
	});

	// Activate Loading widget on pageclose/navigation/ajax
	$(window).on('beforeunload', function(){ showLoading(); });
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
	$('body').on('click', '.link2model', function(e){
		var atts = ($(this)._for()||'').split('|');
		atts[0] && getPage(e, atts[0], [atts[1]]);
		return false;
	});

	// Highlighting table rows and other elements
	$('body').on('mouseenter', '.highlight', function(){
		$(this).effect('highlight', {color: '#f0f0e6'}, 300);
	});

	// Activate agenda eventUnits
	$('body').on('click', '.eventUnit', function(){
		var id = $(this).find('input[type="hidden"]').val();
		$('<div />').load('index.php?load=eventInfo&id='+id, function(){
			this.dialog({width:650, modal:true});
		}).appendTo('body');
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
		var fixTitles = function(){
			$('#listTable').trigger('modified');
			$('.listWrapper').trigger('fill');
		};
		// Sometimes it takes some time to get the position/dimension right
		fixTitles && setTimeout(fixTitles, 200) && setTimeout(fixTitles, 1000);
	});

	// Actions on content load
	$('body').on('contentload', function(){
		// Apply styles to browse buttons (input type=file)
		var browse = $('<div />', {'class': 'browse_box'})
			.append($('<input type="text" />')
				.attr({'class': 'browse_txt', 'disabled': true}))
			.append($('<input type="button" value="Examinar..." />')
				.attr({'class': 'browse_btn'}))
			.append($('<div />')
				.attr({'class': 'browse_hdn'}));

		$(':file:not(.file_styled)').each(function(i, btn){
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

	// Activate Snippets
	$('body').on('snippets', function(){
		$('snippet[initialized!="true"]').each(function(){
			this.attr('initialized', 'true');
			Snippets.add(new Snippet(this));
		}, true);
	});
});


/******************************************************************************/
/********************************* L O G I N **********************************/
/******************************************************************************/

// Login page only
$(function(){
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
/****************************** S N I P P E T S *******************************/
/******************************************************************************/

/**
 * Central store of loaded Snippet objects
 */
var Snippets = {
	members: {},

	add: function(Snippet){
		var groupId = Snippet.getGroupId();

		if (groupId)
		{
			this.members[groupId] || (this.members[groupId] = {});
			this.members[groupId][Snippet.getType()] = Snippet;
		}
	},

	get: function(groupId, type) {
		return (this.members[groupId]||{})[type];
	}
}



/**
 * Snippet Constructor
 */
function Snippet(el){
	var params = $.parseJSON(el.attr('params')),
	    model = params.model,
	    type = params.snippet,
	    groupId = params.groupId;

	/**
	 * Public methods (getters)
	 */
	this.getType    = function(){ return type;    };
	this.getModel    = function(){ return model;    };
	this.getGroupId = function(){ return groupId; };

	/**
	 * Private "methods"
	 */
	function my(sel) {
		return el.find(sel);
	}

	function linked(type) {
		return Snippets.get(groupId, type);
	}

	function resetBigToolsState(btns) {
		linked('bigTools') && linked('bigTools').disable().enable(btns||true);
	}

	/**
	 * Load a new snippet
	 */
	function getSnippet(snippet, opts) {
		xajax_getSnippet(snippet, model, $.extend({}, params, opts));
	}

	/**
	 * Request a new Snippet from the server, or perform an action
	 */
	function request(snippet, filters) {
		var ask = {deleteItem: '¿Realmente desea eliminar este elemento?',
		           blockItem: '¿Realmente desea bloquear este elemento?'};
		if (!ask[snippet] || confirm(ask[snippet])) {
			getSnippet(snippet, {filters: filters, writeTo: ''});
		}
	}

	/**
	 * Snippet especialized initializers
	 */
	var methods = {
		comboList: function() {
			my('.comboList').change(function(){
				$(this).val() && request('viewItem', $(this).val());
			});
		},

		bigTools: function() {
			var btns = $.extend(my('[btn][class!="btOff"]'), {
				// Empty code: all btns; string or array: listed btn(s)
				get: function(code) {
					var set = code ? btns.filter(':not([btn])') : this;
					code && $.each($.isArray(code) ? code : [code], function(){
						set = set.add(btns.filter('[btn="'+this+'"]'));
					});
					return set;
				}
			}).click(function(){
				var axn = $(this).attr('btn'),
				    uid = my('[btn]').attr('uid') || '';
				$(this).hasClass('btOn') && request(axn + 'Item', uid);
			});

			this.disable = function(code) {
				btns.get(code).removeClass('btOn');
				return this;
			};

			this.enable = function(code) {
				var btn = (code === true) ? this.enable.ss : btns.get(code);
				this.enable.ss = btn.addClass('btOn');
				return this;
			};

			this.id = function(uid) {
				return uid ? (this.uid = uid) && this : this.uid;
			};

			this.enable.ss = $();
		},

		commonList: function() {
			resetBigToolsState(['create']);

			my('.listWrapper').on('fill', function(){
				// Store horizontal position and width of each cell...
				my('tbody tr:first td').each(function(i){
					my('.listTitles div:eq('+i+')')
						.width($(this).width())
						.css('left', $(this).position().left);
				});
			});

			my('.innerListRow').click(function(){
				my('.selectedListRow').removeClass('selectedListRow');
				$(this).addClass('selectedListRow');
				if (linked('bigTools')) {
					linked('bigTools').enable().id($(this)._for());
				}
			});

			my('.innerListRow').dblclick(function(){
				// Create new embeddedView if there was none in current row
				if (!$(this).next().find('.embeddedView').remove().length) {
					my('.embeddedView').parents('tr').remove();

					$('<td />', {
						'id': 'embed_' + groupId,
						'class': 'embeddedView',
						'colspan': $(this).find('td').length
					}).on('embed', function(){
//						this.scrollIntoView();
					}).appendTo($('<tr />').insertAfter(this));

					// Request the embeddedView content
					getSnippet('snp_viewItem', {filters: {modifier: $(this)._for()},
					                            writeTo: 'embed_' + groupId});
				}
			});

			my('.innerListTools').on('click', '[alt]', function(){
				var uid = $(this).parents('.innerListRow')._for();
				request($(this)._alt() + 'Item', uid);
			});

			my('.listWrapper').trigger('fill');
		},

		simpleList: function() {
			// TODO
		},

		viewItem: function() {
			resetBigToolsState(['list', 'create', 'edit', 'delete']);
			$('#embed_'+groupId).trigger('embed');
		},

		createItem: function(editing){
			// BigTools buttons
			editing && resetBigToolsState(['create', 'view']);
			linked('bigTools').enable('list'); // Enabled either way

			// Form submitting
			my('.snippet_createForm').submit(function(){
				var filters = $(this).serializeJSON();
				return request(editing ? 'edit' : 'create', filters) & false;
			});

			this.tooltip = function(field, msg) {
				var tgt = my('.snippet_createForm [name="'+field+'"]');
				showTip(tgt, msg, 'bottom left', '.snippet_createForm');
			};
		},

		editItem: function() {
			this.createItem(true);
		}
	}

	// Call the handler method of this Snippet
	methods[type] && methods[type].call(this);
}


/******************************************************************************/
/**************************** D E P R E C A T E D *****************************/
/******************************************************************************/

$(function(){ IN_FRAME && Frames.initialize(); });

Frames = {
	frames: [],
	initialize: function(){		/* Called by an actual frame's onload event */
		var that = this;
		var close = function(){
			window.parent.Frames.garbageCollect(window.frameElement._id());
		}
		$('body').escape(close);
		$('#FrameCloseBtn').click(close);
		$(window.parent).resize(function(){
			that.fixDimensions();
		});
		this.fixDimensions();
		window.frameElement.show();
	},
	loadPage: function( href ){
		var id = this.frames.length, d = document;
		var ifr = this.frames[id] = $('<iframe />').appendTo('body');
		ifr.className = 'ifrForShowPage';
		ifr.src = href + '&iframe';
		ifr.uID = id;
	},
	fixDimensions: function(){
		var h = Math.round($(window.frameElement).height() * 0.8);
		var w = Math.round($(window.frameElement).width() * 0.9);
		$('#frameArea').height(h).width(w);
		$('#frameTitle').width(w);
		$('#frameContent').css('marginTop', h/8 - 30);
		$('#notifications').css('width', w + 10);
	},
	close: function(msg, code){
		msg && parent.say(msg, code||0);
		parent.Frames.garbageCollect(window.frameElement.uID);
	},
	garbageCollect: function(id) {
		$('#iframe#'+id+', iframe[name="'+id+'"]').remove();
	}
}

function closeAgendaEvent(id, msg, resched){
	(typeof resched === 'undefined') && (resched = $('#showRescheduled').attr('checked'));

	var action = resched ? 'cancelar' : 'cerrar';
	var res = prompt('Escriba un comentario para ' + action + ' el evento:', msg||'');

	if (res === '') {
        return say('Se debe incluir un comentario al ' + action + ' un evento.');
    } else if(res) {
        var cfm = "Se dispone a " + action + " el evento con el siguiente mensaje:\n\n" + res +
                  "\n\nPulse Cancelar para editar el mensaje o Aceptar para confirmar.";
        if (confirm(cfm)) {
            xajax_closeAgendaEvent(id, res, resched ? 1 : 0);
        } else {
            closeAgendaEvent(id, res, resched);
        }
    }
}

function initializeList(model, modifier, src){
	$('#listWrapper').prop('update', function(){
		$('#listTable').trigger('modified');

		$('.listRows').click(function(e){
			$(this)._for() && getPage(e, model + 'Info', [$(this)._for()]);
		});

		$('.tblTools').click(function(e){
			var axn = $(this).attr('axn');
			var id = $(this)._for();

			switch (axn) {
				case 'delete':
					if( confirm('¿Realmente desea eliminar este elemento?') ){
						window['xajax_delete' + $.capitalize(model)](id, modifier);
					};
					break;
				case 'block':
					if( confirm('¿Realmente desea bloquear este elemento?') ){
						window['xajax_block' + $.capitalize(model)](id, modifier);
					};
					break;
				default:
					getPage(e, axn + $.capitalize(model), [id, modifier]);
					break;
			};

			return false;
		});
	});
}

function initializeSimpleList() {
	function SimpleList(list, model, modifier) {     // Simple List Constructor
		var my = function(sel) {
			return list.find(sel);
		}
		this.inputs = my('.addItemToSimpleList :input');

		this.createItem = function() {
			var data = {SL_ID: my('.selectedRow')._id()||''};
			this.inputs.each(function(){
				data[this._name()] = this.val();
			}, true);
			var func = 'xajax_create' + $.capitalize(model);
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

	$('.simpleList').each(function(){
        if (!this.find('.addItemToSimpleList')) {
			return false;
		}
        var params = this._for().split('|'),
		    model = params[0],
		    modifier = params[1],
		    SL = new SimpleList(this, model, modifier);

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
			switch ($(this).attr('axn')) {
				case 'create':
					return SL.createItem();
				case 'edit':
					return SL.enableEditItem($(this)._for());
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

			var func = 'xajax_' + $(this).attr('axn') + $.capitalize(model);
			if( !window[func] ) {
				throw('Function ' + func + ' is not registered!');
			}

			return window[func]($(this)._for(), modifier) & false;
		});
	});
};

$(function(){
	// Old common list
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

	// Old comboList widget
	$('body').on('change', '.comboListOld', function(e){
		getPage(e, $(this)._for() + 'Info', [$(this).val()]);
	});
});