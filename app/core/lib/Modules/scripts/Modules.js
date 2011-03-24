/* Pending: #comboList should rely only on Modules (currently it calls getPage) */


function SyncTitles(el, atts){
	var that = this;
	var oBox = el.getElement('.listTitles');
	var oTitles = oBox.getElements('DIV')||[];
	// No need to attempt to sync titles each time if there's no titles
	this.sync = !oTitles.length ? function(){} : function(){
		var oRow = el.getElement('.listWrapper').getElement('TR')||{};
		// Store horizontal position and width of each cell...
		var posX = [];
		Array.from(oRow.cells||[]).forEach(function(cell){
			posX.push( cell.getPosition().x );
		});
		// ...and apply that info to titles
		var startX = posX.length ? posX[0] - 5 : null;
		var availWidth = parseInt(oBox.getStyle('width'));
		var defWidth = availWidth / (oTitles.length||1) - 10;
		oTitles.forEach(function(oTtl, i){
			oTtl.setStyles({
				left: (posX[i] || (startX + availWidth)) - startX,
				width: oRow ? (posX[i+1] ? posX[i+1]-posX[i] : 0) : defWidth
			});
		});
		return that;
	};
};

var Modules = {
	/**************************************************************************/
	/********************************* TOOLS **********************************/
	showError: function( msg ){
		return showStatus( msg );
	},
	/**************************************************************************/
	/********************************* COMMON *********************************/
	initialize: function( type ){
		var Elements = [], that = this;
		// Collect uninitialized elements of type 'type', ignore the rest
		$$('.Modules_Element.Wrapper_of_Type_'+type).forEach(function(dom){
			if( dom.initialized ) return;
			dom.initialized = true;
			dom.Atts = {type:type};
			dom.getElement('FORM').getElements('INPUT').forEach(function(inp){
				dom.Atts[inp.name] = inp.value;
			});
			// Remember params came as a JSON string
			dom.Atts.params = eval('('+dom.Atts.params+')');
			Elements.push( {dom:dom, atts:dom.Atts} );
		});
		// We call the handler method on each element
		Elements.forEach(function(el){
			that[type].call(that, el.dom, el.atts);
		});
	},
	/**************************************************************************/
	/******************************** HANDLERS ********************************/
	info: function(el, atts){									  /*** INFO ***/
		return test( atts );
	},
	comboList: function(el, atts){							/*** COMBO LIST ***/
		$$('.comboList').forEach(function(cl){
			cl.onchange = function(e){
				getPage(e, atts.code + 'Info', [this.value]);
			};
		});
	},
	commonList: function (el, atts){					   /*** COMMON LIST ***/
		// Enable column search and do a first search (without filters)
		new ListSearch(el, atts).updateList();
		// SyncTitles applies to commonList, but is called by updateCommonList,
		// so it needs to be made available to updateCommonList on list update
		el.getElement('.listWrapper').ST = new SyncTitles(el, atts).sync();
	},
	updateCommonList: function(el, atts){
try{
		// updateCommonList is usually loaded within a commonList, so it might
		// need to sync titles width with it (among other stuff)
		var oWrapper = el, ST = null;
		while( !ST && (oWrapper=oWrapper.parentNode) ) ST = oWrapper.ST;
		if( ST ) ST.sync();
		return;
		Modules.fixTableHeader( $('listWrapper') );
		$('listWrapper').getElements('.listRows').forEach(function(row){
			row.addEvent('mouseover', function(){ highLight(this); });
			var id = row.getAttribute('FOR');
			if( id ) row.addEvent('click', function(e){
				getPage(e, this.code + 'Info', [id]);
			});
		});
		$$('.tblTools').forEach(function(tool){
			var axn = tool.getAttribute('AXN');
			var id = tool.getAttribute('FOR');
			tool.addEvent('click', function(e){
				e.stop();
				switch( axn ){
					case 'delete':
						if( confirm('¿Realmente desea eliminar este elemento?') ){
							var handler = window['xajax_delete' + this.code.capitalize()];
							if( handler ) handler(id, this.modifier);
						};
						break;
					case 'block':
						if( confirm('¿Realmente desea bloquear este elemento?') ){
							var handler = window['xajax_block' + this.code.capitalize()];
							if( handler ) handler(id, this.modifier);
						};
						break;
					default: 
						getPage(e, axn + this.code.capitalize(), [id, this.modifier]);
						break;
				};
			});
		});
}catch(e){ test(e); };
	},
	simpleList: function(el, atts){
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
	}
};

function ListSearch(el, atts){
	var that = el.ListSearch = this;
	// Define most used elements as private properties
	var oBox = el.getElement('.listSearch');
	var oBoxLbl = oBox.getElement('SPAN');
	var oInput = oBox.getElement('INPUT');
	var oList = el.getElement('.listWrapper');
	// Create cache box
	var oCache = new Element('DIV', {style:'display:none'}).inject(document.body);
	// Public methods
	this.show = function( btn ){
		oInput.value = '';
		oBoxLbl.innerHTML = btn.alt;
		var left = btn.getPosition().x - 100;
		var top = btn.getPosition().y + 60;
		oBox.setStyles({left:left, top:top, display:'block'});
		oBox.code = btn.get('code');
		oInput.focus();
	};
	this.hide = function(){
		oBox.setStyle('display', 'none').code = null;
		that.updateList();	// Clear filters upon closing
	};
	this.updateList = function( filters ){
		var type = 'update' + atts.type.capitalize();
		var uID = newSID().toString();
		oList.id = 'listWrapper_' + uID;
		var params = {uID:uID, filters:filters||[], src:atts.params.src||''};
		xajax_ModulesAjaxCall(type, atts.code, atts.modifier, params);
	};
	// Add search buttons to each title field and set them up
	el.getElement('.listTitles').getElements('DIV').forEach(function(field){
		if( !field.innerHTML ) return;	// Only for used title fields
		var code = field.getAttribute('FOR');
		new Element('IMG', {
			'code': code,
			'class': 'listSearchBtn',
			'src': 'app/images/buttons/search.gif',
			'alt': field.innerHTML.toLowerCase(),
			'title': 'filtrar por campo ' + field.innerHTML.toLowerCase()
		}).inject( field ).addEvent('click', function(){
			that[(oBox.code == code) ? 'hide' : 'show']( this );
		}).parentNode.setStyle('display', 'block');
	});
	// Attach event handlers
	oInput.addEvent('escape', this.hide);
	oBox.getElement('.CloseButton').addEvent('click', this.hide);
	oInput.addEvent('keyup', function(){
		var newSrch = oInput.value.replace('*', '%');
		if( newSrch != oBox.lastSrch ){
			var aux = {};
			aux[oBox.code] = oBox.lastSrch = newSrch;
			that.updateList( aux );
		};
	});
};