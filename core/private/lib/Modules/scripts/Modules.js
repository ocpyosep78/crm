/**
 * ModulesPortability is a layer between Modules and external objects/tools.
 * You can edit these methods and properties to your own external tools
 */
var ModulesPortability = {
	// Map #showError to a function that handles warning/error output for the
	// user. It receives only one parameter: an error description string.
	// By default, it uses AppTemplate's built-in function showStatus(msg, type)
	showError: function(){ showStatus.apply(window, arguments); },
	// Map #printHTML to the function that will request a page to be printed
	// through ajax.
	// By default, that is xajax_ajaxPrintPage
	printHTML: function(){ xajax_ajaxPrintPage.apply(window, arguments); },
	getPage: function(e, code, type, modifier, params){
		getPage(e, code + type.capitalize(), params);
	},
	doAction: function(){ xajax_ajaxDo.apply(window, arguments); }
};



var Modules = {
	/**************************************************************************/
	/********************************* TOOLS **********************************/
	showError: ModulesPortability.showError,
	printHTML: ModulesPortability.printHTML,
	getPage: ModulesPortability.getPage,
	do: ModulesPortability.doAction,
	/**************************************************************************/
	/********************************* COMMON *********************************/
	setImgPath: function( path ){
		window.MODULES_IMAGES = path||'core/private/lib/Modules/static/images/';
	},
	initialize: function( type ){
		var Elements = [];
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
		Elements.forEach(function(el){ this[type](el.dom, el.atts); }, this);
	},
	/**************************************************************************/
	/******************************** HANDLERS ********************************/
	info: function(el, atts){									  /*** INFO ***/
		return;
	},
	comboList: function(el, atts){							/*** COMBO LIST ***/
		var that = this;
		$$('.comboList').forEach(function(cl){
			cl.evt = cl.addEvent('change', function(e){
				that.getPage(e, atts.code, 'info', atts.modifier, [this.value]);
			});
		});
	},
	commonList: function (el, atts){					   /*** COMMON LIST ***/
		// Enable column search and do a first search (without filters)
		new ListSearch(el, atts).updateList();
		// SyncTitles applies to commonList, but is called by innerCommonList,
		// so it needs to be made available to innerCommonList on list update
		el.getElement('.listWrapper').ST = new SyncTitles(el, atts).sync();
	},
	bigTools: function(el, atts){
		var tools = [];
		el.getElement('.bigTools').getElements('[tool]').forEach(function(tool){
			tools[tool.getAttribute('TOOL')] = tool;
			tool.addEvent('click', function(e){
				if( !this.hasClass('bigToolEnabled') ) return;
				alert('clicked!');
			});
		});
		var bigTools = {
			enableTool: function( tl ){
				if(tl && tools[tl]) tools[tl].addClass('bigToolEnabled');
			},
			disableTool: function( tl ){
				if(tl && tools[tl]) tools[tl].removeClass('bigToolEnabled');
			}
		};
	},
	innerCommonList: function(el, atts){
		var that = this;
		// innerCommonList is usually loaded within a list, so it might
		// need to sync titles width with it. If that's the case, SyncTitles
		// object should be found in one of the parents, so we search for it
		var oWrapper = el, ST = null;
		while( !ST && (oWrapper=oWrapper.parentNode) ) ST = oWrapper.ST;
		if( ST ) ST.sync();
		// Add highlight effects to list's rows
		listRowsHighlight( el );
		// Configure tools behavior: set which ones need confirmation
		var ask = { delete: '¿Realmente desea eliminar este elemento?',
					block: '¿Realmente desea bloquear este elemento?' };
		var sendRequest = function(axn, id){
			(!ask[axn] || confirm(ask[axn]))
				&& that.do(axn, atts.code, atts.modifier, id);
		};
		// Add event handlers
		el.getElements('.listRows').forEach(function(row){
			var id = row.getAttribute('FOR');
			if( !id ) return;
			// Link to info page for each item row
			row.addEvent('click', function(e){
				that.getPage(e, atts.code, 'info', atts.modifier, [id]);
			});
			// Links for each row's tools
			row.getElement('.innerListTools').addEvent('click', function(e){
				e.stop();
				if( e.target.nodeName.toLowerCase() != 'img' ) return;
				sendRequest(e.target.getAttribute('TOOL'), id);
			});
		});
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



/**
 * @overview: adds animation to lists' rows on mouseover
 * @arguments: Element:el[, string:fromColor[, string:toColor]]
 * @returns: the list passed as first argument
 * @notes: can call it with different params as many times as you wish
 *         it will destroy previous handler and create one with new params
 * @disclaimer: this tool is tied to AppTemplate's library 'Modules', so
 *              it requires mootools and rows need to be of class listRows
 */
function listRowsHighlight(el, from, to){
	el.getElements('.listRows').forEach(function(row){
		row.removeEvent('mouseover', row.ref);
		row.addEvent('mouseover', row.ref=function(){
			this.highlight(from||'#f0f0e6', to||'#e0e0e6');
		});
	});
	return el;
};



/**
 * 
 */
function ListSearch(el, atts){
	/**************************************************************************/
	/********************************* TOOLS **********************************/
	var showError = ModulesPortability.showError;
	var printHTML = ModulesPortability.printHTML;
	// Global reference to self
	var that = el.ListSearch = this;
	// Define most used elements and vars as private properties
	var oBox = el.getElement('.listSearch');
	var oBoxLbl = oBox.getElement('SPAN');
	var oInput = oBox.getElement('INPUT');
	var oList = el.getElement('.listWrapper');
	var oFields = [];
	var type = 'inner' + atts.type.capitalize();
	// Public methods
	this.show = function( btn ){
		that.hide();
		oBoxLbl.innerHTML = btn.alt;
		var left = btn.getPosition().x - 100;
		var top = btn.getPosition().y + 60;
		oBox.setStyles({left:left, top:top, display:'block'});
		oBox.pos = parseInt( btn.get('pos') );
		oBox.code = btn.get('code');
		setTimeout(function(){ oInput.focus(); }, 20);	// Chrome bug, needs the timeout
		return that;
	};
	this.hide = function(){
		if( oBox.lastSrch ) that.updateList();	// Clear filters upon closing
		oBox.setStyle('display', 'none').lastSrch = oBox.code = oInput.value = '';
		return that;
	};
	this.updateList = function( filters ){
		oList.id = 'listWrapper_' + newSID().toString();
		var params = {writeTo:oList.id, filters:filters||[], src:atts.params.src||''};
		printHTML([atts.code, type], atts.modifier, params);
		return that;
	};
	// Add search buttons to each title field and set them up
	el.getElement('.listTitles').getElements('DIV').forEach(function(field, i){
		if( !field.innerHTML ) return;	// Only for used title fields
		var code = field.getAttribute('FOR');
		new Element('IMG', {
			'pos': i,
			'code': code,
			'class': 'listSearchBtn',
			'src': MODULES_IMAGES + 'buttons/search.gif',
			'alt': field.innerHTML.toLowerCase(),
			'title': 'filtrar por campo ' + field.innerHTML.toLowerCase()
		}).inject( field ).addEvent('click', function(){
			that[(oBox.code == code) ? 'hide' : 'show']( this );
		}).parentNode.setStyle('display', 'block');
		oFields[i] = field;
	});
	// Attach event handlers
	oBox.getElement('.CloseButton').addEvent('click', this.hide);
	oInput.addEvents({
		keyup: function(e){			// Input or escape
			if( e.key == 'esc' ) return that.hide();
			var newSrch = oInput.value.replace('*', '%');
			if( newSrch != oBox.lastSrch ){
				var aux = {};
				aux[oBox.code] = oBox.lastSrch = newSrch;
				that.updateList( aux );
			};
		},
		keydown: function(e){		// Tab or shift+tab
			if( e.key != 'tab' ) return;
			var pos = parseInt(oBox.pos) + (e.shift ? -1 : 1);
			var oTgt = (pos < 0) ? oFields.getLast() : (oFields[pos] || oFields[0]);
			return !that.show( oTgt.getElement('.listSearchBtn') );	// returns false
		}
	});
};



/**
 * 
 */
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
			posX.push( $(cell).getPosition().x );
		});
		// ...and apply that info to titles
		var startX = posX.length ? posX[0] : null;
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
	// Re-sync if page is resized or menu is hidden
	window.addEvent('resize', that.sync);
	window.addEvent('menutoggled', that.sync);
};