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

// Add serializeJSON (thanks Arjen Oosterkamp)
jQuery.fn.serializeJSON = function() {
	var json = {};
	jQuery.map(this.serializeArray(), function(n){
		json[n.name] = n.value;
	});
	return json;
}

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
var jDirectMethods = ['id', 'name', 'class', 'for', 'rel', 'title',
                      'src', 'alt', 'type', 'target', 'method'];
jQuery.map(jDirectMethods, function(attr){
	jQuery.fn['_'+attr] = function(val){
		return this.attr.apply(this, jQuery.merge([attr], arguments));
	};
});

// Add method print to jQuery objects
jQuery.fn.print = function() {
	this.get(0) && this.get(0).print();
	return this;
}

jQuery.capitalize = function(txt) {
	return txt.replace(/\b[a-z]/g, function(x){ return x.toUpperCase(); });
}






















/*************************************************************************************************/
/************************************ M O O T O O L S   E X T ************************************/
/*************************************************************************************************/

Element.Events.enter = {
    base: 'keyup',
    condition: function(e){ return e.key == 'enter'; }
};
Element.Events.escape = {
    base: 'keyup',
    condition: function(e){ return e.key == 'esc'; }
};

Element.implement({
	unselectable: function(){
		if( typeof(this.onselectstart) != 'undefined' ){
			this.addEvent('selectstart', function(){ return false; });
		}else if( typeof(this.style.MozUserSelect) != 'undefined' ){
			this.setStyle('MozUserSelect', 'none');
		}else if( typeof(this.style.WebkitUserSelect) != 'undefined' ){
			this.setStyle('WebkitUserSelect', 'none');
		}else if( typeof(this.unselectable) != 'undefined' ){
			this.setProperty('unselectable', 'on');
		}
	},
	flash: function(to, from, reps, prop, dur) {
		prop = prop || 'background-color';
		var effect = new Fx.Tween(this, {
			duration: dur||250,
			link: 'chain'
		});
		for( x=1 ; x<=(reps||1) ; x++ ){
		  effect.start(prop, from, to).start(prop, to, from);
		};
	}
});



/*************************************************************************************************/
/************************************** P R O T O T Y P E S **************************************/
/*************************************************************************************************/

String.prototype.ltrim = function(){ return this.replace(/^\s+/,''); };
String.prototype.rtrim = function(){ return this.replace(/\s+$/,''); };
String.prototype.trim = function(){ return this.ltrim().rtrim(); };
String.prototype.trimX = function(){ return this.replace(/\s+/g,' ').trim(); };
String.prototype.toCaps = function(){ return this.replace(/(^|\s)([a-z])/g,function(m,p1,p2){
	return p1+p2.toUpperCase();});
};
String.prototype.fill = function( i , s , r ){	/* times, fillStr, reverse */
	if( i<0 ){ r = true; i = Math.abs(i); };
	if( i > this.length ) var a=(new Array(i-this.length+1)).join( s||0 );
	else return ( r ) ? this.substr(this.length-i--): this.substr( 0 , i );
	return r ? a+this.toString() : this.toString()+a;
};
if( typeof(HTMLElement) != 'undefined' && HTMLElement.prototype && !HTMLElement.prototype.click ){
	HTMLElement.prototype.click = function(){
		var evt = this.ownerDocument.createEvent('MouseEvents');
		evt.initMouseEvent(
			'click', true, true, this.ownerDocument.defaultView, 1, 0, 0, 0, 0,
			false, false, false, false, 0, null
		);
		this.dispatchEvent( evt );
	};
};

function test( x ){
	if( typeof(x) == 'undefined' ) var op = 'undefined';
	else if( typeof(x) == 'string' ) var op = x;
	else if( typeof(x) == 'array' ) op = '[' + x.join(', ') + ']';
	else if( typeof(x) == 'object' ) op = obj2Str( x );
	else if( x.toString ) op = x.toString();
	else op = x;
	alert( op );
	function obj2Str( x ){
		var y = '{\n';
		for( var z in x ) if( x.hasOwnProperty(z) ) y += z + ': ' + x[z] + ',\n';
		return y + '}';
	};
};

function newSID(){
	return (new Date()).getMilliseconds() + Math.random();
};