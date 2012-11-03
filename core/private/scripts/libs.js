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