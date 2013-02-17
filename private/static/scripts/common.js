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

// Create an element if it doesn't exist
$.fn.touch = function(visible) {
	if (!/^(#|\.|)\w+$/.test(this.selector)) {
		throw ('$.touch should only be used for simple selectors ("#sel", ".sel" or "sel")');
	}

	if (!$(this.selector).length) {
		var types = {'tag': null, '#': 'id', '.': 'class'};
		var type = types[(this.selector.match(/^(#|\.)/)||['tag'])[0]];

		if (type) {
			var attrs = {};
			attrs[type] = this.selector.replace(/^(#|\.)/, '');
			var el = $('<div />', attrs);
		} else {
			var el = $('<' + this.selector + ' />');
		}

		el.toggle(!!visible).appendTo('body');
	}
}

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

// jQuery doesn't have a capitalize method, for some reason
$.capitalize = function(txt) {
	return txt.replace(/\b[a-z]/g, function(x){ return x.toUpperCase(); });
}

// Set defaults for jQuery UI Dialog widget
$.extend($.ui.dialog.prototype.options, {
	modal: true,
	stack: false,
	resizable: false,
	position: { my:'center', at:'center', of:window },
	width: 'auto',
	closeText: 'Cerrar',
	close: function() {
		$(this).dialog('destroy').remove();
	}
});


/******************************************************************************/
/**************************** P R O T O T Y P E S *****************************/
/******************************************************************************/
String.prototype.fill = function(i , s , r){	/* times, fillStr, reverse */
	if( i<0 ){ r = true; i = Math.abs(i); };
	if( i > this.length ) var a=(new Array(i-this.length+1)).join( s||0 );
	else return ( r ) ? this.substr(this.length-i--): this.substr( 0 , i );
	return r ? a+this.toString() : this.toString()+a;
};

function db(x) {
	console.log(x);
	alert('Debug log generated for: ' + x);
}

function empty(x) {
	return (typeof x === 'undefined') || !x;
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
/*************************** C O N T R O L L E R S ****************************/
/******************************************************************************/

function ajax(id)
{
	$.ajax({url: 'ajax',
	        type: 'post',
	        dataType: 'json',
	        data: {id: id,
	               args: $(arguments).toArray().slice(1)},
	        success: function(js) {
				$.map(js, eval);
			}});
}


/******************************************************************************/
/********************************* T O O L S **********************************/
/******************************************************************************/

// Notifications
function say(txt, type, stay) {
	if (!txt) {
		return false;
	}

	var cl = ($.isNumeric(type||0) ? (type ? 'success' : 'error') : type);

	$('#notifications').hide(1)._class(cl + 'Status')
		.find('div:last').html(txt).end()
		.show('drop', {direction:'up', height:400}, 500, function(){
			// Cancel pending hiding and queue a new one (0 == don't hide)
			clearTimeout($(this).data('hto'));
			if ((stay !== 0) && empty(window.SHOW_LOGO_AREA)) {
				$(this).data('hto', setTimeout(function(){
					$('#notifications').fadeOut({queue:false});
				}, (stay||10)*1000));
			}
		});
}

// Loading animation
function showLoading(show){
	$('#loadingGif').toggle(show !== false);
};

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
	var args = $.makeArray(arguments),
	    shift = (args.shift()||{}).shiftKey,
		page = args.shift();

	ajax('content', page, args, shift);
}

// Initialize loaded page and events associated to new elements in it
function iniPage(name) {
	var fn = window['ini_'+name];

	try {
		fn && fn.apply(fn, IniParams.get());
		$('.menuItem.currentPage').removeClass('currentPage');
		$('.menuItem[for="' + name + '"]').addClass('currentPage');
	} catch(e) {
		return DEVMODE ? db(e) : false;
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


/******************************************************************************/
/******************************* D O M R E A D Y ******************************/
/******************************************************************************/

$(function(){
	// Activate NavBar buttons
	$('body').on('click', '.navMod', function(e){
		$('.navCurrMod')._class('navMod');
		$(this)._class('navCurrMod');
		getPage(e, $(this)._rel());
		return false;
	});

	$('body').on('click', '.logout', function(){
		ajax('logout');
	});

	// Menu
	$('#hideMenu').click(hideMenu);
	$('#showMenu').click(showMenu);

	$('body').on('click', '.menuItem', function(e){
		return !getPage(e, $(this)._for()) && false;
	});

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

	// Tabs functionality
	$('body').on('click', '#tabButtons div', function(){
		return ajax('switchTab', $(this)._for()) & false;
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
		ajax('eventInfo', $(this).find('input[type="hidden"]').val());
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
		$('body').trigger('contentload');

		$('snippet[initialized!="true"]').each(function(){
			this.attr('initialized', 'true');
			var Snippet = new Snippet(this);
			// Register it (to be found by its grouped snippets, if any)
			Snippets.add(Snippet);
			// Let the other snippets know that this one's ready
			$('body').trigger('snp.' + Snippet.getKind());
		}, true);

		showLoading(false);
	});

	$('#loggedAs span[userid]').click(function(){
		ajax('snippet', 'simpleItem', 'User', {'id': $(this).attr('userid'),
		                                       'action': 'dialog'});
	}).css('cursor', 'pointer');
});


/******************************************************************************/
/********************************* L O G I N **********************************/
/******************************************************************************/

// Login page only
$(function(){
	var frm = $.forms('formLogin');

	if (frm.length) {
		frm.user.addClass('inputFocused').focus();

		window.SHOW_LOGO_AREA = true;
		$('#notifications').show(1);

		frm.submit(function(){
			if (!frm.user.val()) {
				var msg = 'Debe escribir un nombre de usuario.';
				return frm.user.focus() & say(msg, 'error', 0) & false;
			} else if (!frm.pass.val()) {
				var msg = 'Debe escribir su contraseña.';
				return frm.pass.focus() & say(msg, 'error', 0) & false;
			} else {
				ajax('login', frm.user.val(), frm.pass.val());
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
			this.members[groupId][Snippet.getKind()] = Snippet;
		}
	},

	get: function(groupId, kind) {
		return (this.members[groupId]||{})[kind];
	}
}



/**
 * Snippet Constructor
 */
function Snippet(el){
	var I       = this,
	    params = $.parseJSON(el.attr('params')),
	    kind    = params.kind,
	    model   = params.model,
	    groupId = params.groupId,
	    id      = params.id;

	/**
	 * Public methods (getters)
	 */
	this.getKind    = function(){ return kind;    };
	this.getModel   = function(){ return model;    };
	this.getGroupId = function(){ return groupId; };

	/**
	 * Private "methods"
	 */
	function my(sel) {
		return el.find(sel);
	}

	function linked(kind) {
		return Snippets.get(groupId, kind);
	}

	function _do(action, opts) {
		switch (action) {
			case 'delete':
				var ask = '¿Realmente desea eliminar este elemento?';
				break;
		}

		if (!ask || confirm(ask))
		{
			var args = $.extend({}, params, opts||{}, {action: action});
			action && ajax('snippet', kind, model, args);
		}
	}

	/**
	 * Snippet especialized initializers
	 */
	var methods = {
		comboList: function() {
			my('.comboList').change(function(){
				$(this).val() && _do('viewItem', {'id': $(this).val()});
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
				var btn = $(this).attr('btn');
				$(this).hasClass('btOn') && _do(btn, {'id': id||0});
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

			this.get = function(code) {
				return btns.get(code);
			}

			this.enable.ss = $();

			this.id = function(uid) {
				return uid ? (id = uid) && this : id;
			};

			if (params.parent && linked(params.parent)) {
				this.disable().enable(linked(params.parent).bigTools);
			} else if (params.parent) {
				$('body').on('snp.' + params.parent, function(){
					I.disable().enable(this.bigTools);
				});
			}

			this.id(id||0);
		},

		commonList: function() {
			this.bigTools = ['list', 'create'];

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

				var bigTools = linked('bigTools');
				var disabled = $(this).hasClass('snp_disabled');

				if (bigTools) {
					bigTools.enable().id($(this)._for());
					bigTools.get('restore').toggle(disabled);
					bigTools.get('delete').toggle(!disabled);
				}
			});

			my('.innerListRow').dblclick(function(){
				_do('dialog', {'id': $(this)._for()});
			});

			my('.innerListTools').on('click', '[alt]', function(){
				var uid = $(this).parents('.innerListRow')._for();
				_do($(this)._alt(), {'id': uid});
			});

			$('body').on('snp.bigTools', function(){
				linked('bigTools') && linked('bigTools').get('restore').hide();
				id && my('.innerListRow[for="' + id + '"]').click();
			});

			my('.listWrapper').trigger('fill');
		},

		simpleList: function() {
			// TODO
		},

		createItem: function(){
			// TODO
		},

		editItem: function() {
			$('body').on('snp.bigTools', function(){
				var bigTools = linked('bigTools');
				var disabled = !!my('.snp_disabled').length;

				bigTools.get('restore').toggle(disabled);
				bigTools.get('delete').toggle(!disabled);
			});
		},

		viewItem: function() {
			$('body').on('snp.bigTools', function(){
				var bigTools = linked('bigTools');
				var disabled = !!my('.snp_disabled').length;

				bigTools.get('restore').toggle(disabled);
				bigTools.get('delete').toggle(!disabled);
			});
		},

		simpleItem: function() {
			// Fix image height (if an image is present, that is)
			my('.snp_item_img').height(my('.snp_chunk').height()+16).show();
		},

		tabs: function() {
			var load = function(tab)
			{
				tab.attr('loaded', true);
				_do('load', {'tab': tab._rel()});
			}

			my('.tabs').tabs({beforeActivate: function(event, ui) {
				ui.newPanel.attr('loaded') || load(ui.newPanel);
			}}).show();

			load(my('.tabs div:first'));
		}
	}

	// Call the handler method of this Snippet
	methods[kind] && methods[kind].call(this);
}


/******************************************************************************/
/**************************** D E P R E C A T E D *****************************/
/******************************************************************************/

function closeAgendaEvent(id, msg, resched) {
	(typeof resched === 'undefined') && (resched = $('#showRescheduled').attr('checked'));

	var action = resched ? 'cancelar' : 'cerrar';
	var res = prompt('Escriba un comentario para ' + action + ' el evento:', msg||'');

	if (res === '') {
        return say('Se debe incluir un comentario al ' + action + ' un evento.');
    } else if(res) {
        var cfm = "Se dispone a " + action + " el evento con el siguiente mensaje:\n\n" + res +
                  "\n\nPulse Cancelar para editar el mensaje o Aceptar para confirmar.";
        if (confirm(cfm)) {
            ajax('closeAgendaEvent', id, res, resched ? 1 : 0);
        } else {
            closeAgendaEvent(id, res, resched);
        }
    }
}

function initializeList(model, modifier, src) {
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
						ajax('delete' + $.capitalize(model), id, modifier);
					};
					break;
				case 'block':
					if( confirm('¿Realmente desea bloquear este elemento?') ){
						ajax('block' + $.capitalize(model), id, modifier);
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
			ajax('create' + $.capitalize(model), data, modifier);
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

			var fn = $(this).attr('axn') + $.capitalize(model);
			return ajax(fn, $(this)._for(), modifier) & false;
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









function ini_registerSales() {
	var frm = $.forms('frmOldSales');

	frm.setSeller = function(code){
		this.seller.val(code);
	};
	frm.id_customer.change(function(){
		frm.seller.val(0);
		silentXajax('setSeller', [this.val()]);
	});
	frm.submit(function(){
		alert('pending migration from xajax');
	});
	frm.restart = function(){
		this.reset().find('[name="saleType"]:first').click();
	};

	/* Following code adjusts which element should be disabled depending on
		which type of sale it is (fields being a list of all depending fields) */
	var optionalFields = {	/* each type list includes depending fields to SHOW */
		fields: ['id_system', 'id_installer', 'technician', 'warranty'],
		system: ['id_system', 'id_installer', 'warranty'],
		product: ['warranty'],
		service: ['technician']
	};

	frm.find('[name="saleType"]').click(function(e){
		e.stopPropagation();

		$.each(optionalFields.fields, function(i, field){
			frm[field].attr('disabled', true);
		});
		$.each(optionalFields[$(this).val()], function(i, field){
			frm[field].attr('disabled', false);
		});
	}).parent().click(function(){
		$(this).find('[name="saleType"]').click();
	});

	frm.restart();
}

function ini_home(){
	return ini_agenda();
}

function ini_createEvent(id_event){		/* Agenda */
	function validateTimeInput(el){
		var p = el.val().split(':'),
		    val = p[0].fill(2, 0, true) + ':' + (p[1]||'').fill(2, 0, true),
		    ret = val.test(/^(2[0-3]|[01]\d):[0-5]\d$/) ? val : '';
		return ret && el.val(ret);
	}

	$('#btn_saveEvent').click(function(){
		if ($('#evt_iniTime').val() === '' || !validateTimeInput($('#evt_iniTime'))) {
			return showTip('evt_iniTime', 'Hora de inicio inválida.');
		};	/* Preformat time, validate and apply changes if valid */
		if ($('#evt_endTime').val() !== '' && !validateTimeInput($('#evt_iniTime'))) {
			return showTip('evt_endTime', 'Hora de finalización inválida.');
		};
		if ($.trim($('#evt_event').val()) === '') {
			return showTip('evt_event', 'Debe proporcionar una descripción del evento.');
		};

		alert('pending migration from xajax');
	});

	$('#evt_target').change(function(){
		selectOption($('#remind'), $(this).val(), 'value');
	});
}

function ini_editEvent(){		/* Agenda */
	ini_createEvent($('#id_event').val());
}

function ini_agenda(){
	// Tie events to each day
	$('.agenda_dayWrapper').find('.agenda_dayDate, .agenda_dayName').click(function(){
		$(this).parents('.agenda_dayWrapper').clone(true).dialog();
	});

	$('#agenda_move').find('img').each(function(i, im){
		$(im)._for() && $(im).click(function(e){
			getPage(e, 'agenda', [$(im)._for(), getFilters()]);
		});
	});

	$('#btn_createEvent').click(function(e){
		getPage(e, 'createEvent', []);
	});

	$("#agenda_calendar").datepicker({
		showOn: 'button',
		dateFormat: 'yy/mm/dd',
		autoSize: true,
		showAnim: 'slideDown',
		buttonImage: IMAGES_URL + '/agendaTools/calendar.gif',
		buttonImageOnly: true,
		beforeShow: function(input, inst){
			var itvl = setInterval(function(){
				var selDay = $(inst.dpDiv).find('.ui-datepicker-current-day');
				if (selDay.length)
				{
					selDay.siblings('td').find('a').addClass('ui-state-active');
					clearInterval(itvl);
				}
			}, 20);
		},
		onSelect: function(day, inst){
			getPage('agenda', [day, getFilters()]);
		}
	});

	function getFilters(){
		var filters = {};
		$('.sel_agendaFilters').each(function(i, sel){
			filters[$(sel).attr('filter')] = $(sel).val();
		});
		return filters;
	};

	function filterAgenda(e){
		var resched = $('#showRescheduled').attr('checked') ? 1 : 0;
		getPage(e, 'agenda', [$('#thisDate').val(), getFilters(), resched]);
	};

	$('.sel_agendaFilters').each(function(i, sel){
		$(sel).change(filterAgenda);
	});

	$('.eventRescheduled').toggle($('#showRescheduled').attr('checked'));

	$('#showRescheduled').click(function(){
		$('.eventRescheduled').toggle();
	});

	// Animate Event Tools
	$('.eventUnit:not(.eventClosed)').hover(function(){
		$(this).find('.eventTools img').animate({
			'right': 0
		}, {'duration': 100, 'queue': false});
		$(this).children(':not(.eventTools)').animate({
			'opacity': 0.6
		});
	}, function(){
		$(this).find('.eventTools img').animate({
			'right': -200
		}, {'duration': 500, 'queue': false});
		$(this).children(':not(.eventTools)').animate({
			'opacity': 1
		});
	});

	// Activate Event Tools
	$('.eventTools img').click(function(e){
		var eventID = $(this).parents('.eventUnit:first').find('[name="id_event"]').val();

		switch ($(this)._for())
		{
			case 'edit'  : getPage(e, 'editEvent', [eventID])
				break;
			case 'cancel': closeAgendaEvent(eventID, '', true);
				break;
			case 'close' : closeAgendaEvent(eventID, '', false);
				break;
			default:
				return;
		}
		return false;
	});
}

function ini_agendaDay(){
	function getFilters(){
		var filters = {};
		$('.sel_agendaFilters').each(function(i, sel){
			filters[$(sel).attr('filter')] = $(sel).val();
		});
		return filters;
	};

	function filterAgenda(e){
		var resched = $('#showRescheduled').attr('checked') ? 1 : 0;
		getPage(e, 'agenda', [$('#thisDate').val(), getFilters(), resched]);
	};

	$('.sel_agendaFilters').each(function(i, sel){
		$(sel).change(filterAgenda);
	});

	$('.eventRescheduled').toggle($('#showRescheduled').attr('checked'));

	$('#showRescheduled').click(function(){
		$('.eventRescheduled').toggle();
	});
}

function ini_activity(){
	var msg = "¿Está seguro que desea descartar esta entrada?\n" +
		"Si continúa, el elemento no volverá a aparecer en esta lista.";

	$('.closeActivityEntry').each(function(i, btn){
		$(btn)._for() && $(btn).click(function(){
			confirm(msg) && ajax('closeActivityEntry', $(btn)._for());
		});
	});
}

function ini_activity_technical(){ ini_activity(); }

function ini_activity_sales(){ ini_activity(); }

function ini_createTechVisits( data ){
	/* Show form as soon as picture is fully loaded */
	function showForm(){
		clearTimeout(to);
		$('#technicalForm').show();
		$('#tch_custNumber').focus();
	};
	var to = setTimeout(showForm, 2000);		/* Just in case pic loaded already (should not, but...) */
	$('#technicalFormBg').load(showForm);

	/* Collection of methods, attached to a DOM element (to be called through ajax) */
	var TechnicalForm = $('#technicalForm').get(0).handler = {
		frm: {},
		ss: {},
		ini: function(data){
			var that = this;
			var frm = this.frm = $.forms('frm_newTechVisit', 'tch_');

			frm.ifIncomplete.keyup(function(){
				frm.complete._checked(true);
			});

			frm.complete.mousedown(function(){
				var check = $(this);
				check._checked() && setTimeout(function(){
					check._checked(false);
				}, 100);
			});

			/* Attach event handlers to search boxes */
			$('.tchSearch').each(function(){
				var by = this._id().replace(/^tchSrch_/, '');

				this.click(function(){
					that.search.call(that, by);
				});

				frm[by].keydown(function(e){
					return (e.which != 13);
				});

				frm[by].enter(function(){
					that.search.call(that, by);
				});
			}, true);

            if (data.id_customer && !data.id_sale) {
                ajax('tchFormAcceptSale', '', data.id_customer);
            } else if(data) {
                this.fillForm(data, !data.id_sale);
            }
		},
		/* data is sent as parameter if provided, otherwise the whole form is sent */
		search: function(by){
			this.frm[by] && ajax('tchFormSuggest', by, this.frm[by].val());
		},
		clearSuggest: function(){
			$('#tch_suggest').html('');
		},
		/* data is a JSON 2-dimensional object: first level is customers,
			second level is invoices (plus detail) */
		suggest: function(data){
			this.clearSuggest();

			if (typeof(data) != 'object') return;

			if (data.length > 30)
			{
				data = data.slice(0, 30);

				var msg = 'Listado Parcial (mostrando los primeros 30 resultados)';
				$('#tch_suggest').html("<div class='tch_s_notice'>" + msg + "</div>");
			}
			else if (!data.length)
			{
				var msg = 'No hay resultados que coincidan con su búsqueda';
				$('#tch_suggest').html("<div class='tch_s_empty'>" + msg + "</div>");
			}

			$.map(data, this.addCustomersSuggest);
		},
		addCustomersSuggest: function(data){	/* Builds the list of suggested invoices/sales/installs */
			var customer = data['customer'];
			var rows = data['rows'];

			$('#tch_suggest').html($('#tch_suggest').html() +
				"<div class='tch_s_customer'>Cliente " + data.customer + "</div>" +
				"<div class='tch_s_contact'>" +
				(data.contact ? 'Contacto: ' + data.contact + '<br />' : '') +
				"</div>" +
				"<div class='tch_s_row tch_s_noInvoice' cust='" + data.id_customer + "'>" +
				"Servicio Técnico sin factura previa</div>");

			$.each(rows, function(){
				$('#tch_suggest').html($('#tch_suggest').html() +
					"<div class='tch_s_row' for='" + this.onSale + "'>" +
					"Factura: " + this.invoice +
					(this.system ? ' (' + this.system + ")" : '') +
					' | Garantía vence: ' + this.warrantyVoid +
					(this['void'] ? ' <strong>(vencida)</strong>' : '') +
					(this.notes ? '<br /><em>&nbsp;&nbsp;Más información: ' + this.notes + '</em>' : '') +
					'</div>');
			});

			$('#tch_suggest .tch_s_row').click(function(){
				ajax('tchFormAcceptSale', $(this)._for()||'', $(this).attr('cust')||'');
			});
		},
		fillForm: function(data, auto){	/* 'auto' means a script called, not the user */
			if (typeof(data) === 'object') {
				var frm = this.frm;

				$.each(data, function(key, val){
					if (frm[key]) {
						(frm[key]._type() == 'radio')
							? frm[key].filter('[value="'+val+'"]')._checked(true)
							: frm[key].val(val);
					}
				});

				$('#tch_id_system').attr('disabled', !!data.onSale);

				// Show Save and Print buttons
				auto || this.showButtons();

				// Take a snapshot of current customer's data
				this.takeSnapshot(data);
			}
		},
		showButtons: function(show){	/* to hide, pass false as param */
			$('#tch_buttons, #tch_submit').toggle(show);
		},
		submit: function(){
			if (!$('#tch_buttons:visible').length) return;

			if( !this.checkSnapshot() ){
				var msg = 'ATENCIÓN:\n\n' +
					'Algunos datos del cliente fueron cambiados sin mediar\n' +
					'confirmación. El contacto puede ser editado libremente, pero\n' +
					'no así los restantes datos del cliente.\n\n' +
					'Si desea elegir un cliente diferente, realice la búsqueda por\n' +
					'cualquiera de los campos habilitados y seleccione un elemento\n' +
					' de la lista de sugerencias.\n\n' +
					'Pulse Aceptar para recargar los datos correspondientes a su\n' +
					'Última selección, o Cancelar para elegir nuevamente un cliente\n' +
					'o factura.';
				return confirm(msg) ? this.restoreFromSnapshot() : null;
			};

			alert('pending migration from xajax');
		},
		select: function(field){
			$('#tch_'+field).focus().select();
		},
		/* SNAPSHOT (security check to make sure the user is saving exactly what he sees */
		takeSnapshot: function(data){
			this.ss = {
				custNumber:	data['custNumber'],
				customer: data['customer'],
				address: data['address'],
				phone: data['phone']
			};
		},
		getSnapshot: function(){
			return this.ss;
		},
		checkSnapshot: function(){
			var ret = true;
			var frm = this.frm;
			$.each(this.ss, function(key, ss){
				ret = ret && (ss == frm[key].val());
			})
			return ret;
		},
		restoreFromSnapshot: function(){
			var frm = this.frm;
			$.each(this.ss, function(key, ss){
				frm[key] && frm[key].val(ss);
			});
		}
	};

	TechnicalForm.ini(data||[]);

	/* Enable save and print buttons */
	$('[name="frm_newTechVisit"]').submit(function(){
		return TechnicalForm.submit() & false;
	});
	$('#tch_save').click(function(){
		return TechnicalForm.submit() & false;
	});
	$('#tch_print').click(function(){
        /* TODO */
	});

};
function ini_editTechVisits( data ){ ini_createTechVisits( data ); };

function ini_techVisitsInfo( id ){
	var src = URL_PUB + '/export/pdf/techVisit.php?id=' + id;

	$('#techVisitsPDF')._src(src + '#toolbar=0&navpanes=0&scrollbar=0');
	$('#techVisitsPrintPDF')._src(src + '&printer#toolbar=0');

	$('#btn_techVisitsEdit').click(function(e){
		getPage(e, 'editTechVisits', [id]);
	});

	$('#btn_techVisitsPrint').click(function(){
		window.frames.fra_techVisitsPrintPDF.print();
	});

    // AdminTechNotes
    $('#saveAdminTechNotes').click(function(){
		ajax('saveAdminTechNotes', id, $('#adminTechNotes textarea').val()||'');
	});
};