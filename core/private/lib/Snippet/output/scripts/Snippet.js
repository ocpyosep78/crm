SNIPPET_IMAGES = 'core/private/lib/Snippet/output/images';

/**
 * SnippetPort is a layer between Snippet and external objects/tools.
 * You can edit these methods and properties to your own external tools
 */
var SnippetPort = {
	// Map #showError to a function that handles warning/error output for the
	// user. It receives only one parameter: an error description string.
	// By default, it uses AppTemplate's built-in function showStatus(msg, type)
	showError: function(){ showStatus.apply(window, arguments); },
	// Map #addSnippet to the function that will request a page to be printed
	// through ajax.
	// By default, that is xajax_addSnippet
	addSnippet: function(){ xajax_addSnippet.apply(window, arguments); },
	getPage: function(e, code, snippet, params){
        return alert(code + snippet.capitalize());
		getPage(e, code + snippet.capitalize(), params);
	}
};



var Snippet = {
	/**************************************************************************/
	/********************************* GROUPS *********************************/
	groups: {},
	/**************************************************************************/
	/********************************* TOOLS **********************************/
	showError: SnippetPort.showError,
	addSnippet: SnippetPort.addSnippet,
	getPage: SnippetPort.getPage,
	showToolTip: function(uID, snippet, code, msg){
		var elView = this.groups[uID][snippet].el;
		if( elView ) elView.TTT.show(code, msg);
	},
	hideToolTip: function(uID, snippet){
		var elView = this.groups[uID][snippet].el;
		if( elView ) elView.TTT.hide();
	},
	/* sendRequest is used by different buttons (bigTools, listRowTools, etc.) */
	sendRequest: function(snippet, atts, filters){
		// Configure btns behavior: set which ones need confirmation
		var ask = { deleteItem: '¿Realmente desea eliminar este elemento?',
					blockItem: '¿Realmente desea bloquear este elemento?' };
		// Merge default params over original params
		var toAdd = {filters:filters||'', writeTo:'', initialize:1};
		var params = Object.merge(Object.clone(atts.params), toAdd);
		// Request confirmation if set, then request snippet if not aborted
		(!ask[snippet] || confirm(ask[snippet]))
			&& this.addSnippet(snippet, atts.code, params);
	},
	// Searchs for corresponding bigTools snippet. If found, it first disables
	// all buttons, then it attempts to enable buttons described in parameter
	// btns. This could be an array of btn codenames, or a single codename as
	// array. For buttons that need a uID, pass it as fourth parameter.
	// When btns is passed, it is recorded for subsequent calls that don't
	// include a btns parameter, thus serving for initializations of the
	// snippet's state.
	resetBigTools: function(el, atts, btns, uID){
		var BT = (this.groups[atts.params.group_uID]||{})['bigTools'];
		if( !BT || !BT.el ) return;
		BT.el.allBtns('disable');
		// Make it an array if it's a string
		if(typeof(btns) != 'object' && btns) btns = [btns];
		// If omitted, take the last recorded snapshot. Walk its members
		(btns||BT.el.lastSS||[]).forEach(function(btn){
			BT.el.enable(btn||null, uID||null);
		});
		// Save snapshot as initial state
		if( btns ) BT.el.lastSS = btns;
	},
	/**************************************************************************/
	/********************************* COMMON *********************************/
	initialize: function( snippet ){
try{
		var that = this;
		var Elements = [];
		// Collect uninitialized snippets of type 'snippet', ignore the rest
		$$('.Snippet.Wrapper_for_'+snippet).forEach(function(el){
			if( el.initialized ) return;
			el.initialized = true;
			var atts = {snippet: snippet};
			el.getElement('FORM').getElements('INPUT').forEach(function(inp){
				atts[inp.name] = inp.value;
			});
			// atts.params came as a JSON string, eval it
			atts.params = eval('('+atts.params+')');
			// Store this snippet in Snippet's @groups, grouped by group_uID
			var entry = {el: el, atts: atts};
			var uID = atts.params.group_uID;
			uID && ((that.groups[uID]=that.groups[uID]||{})[snippet] = entry);
			// Call the handler method on each element
			that[snippet].apply(that, Object.values(entry));
			// See if it was embedded and given an initialize function
			var dad = el.getParent('[id=embed_'+uID+']');
			if( dad ) dad.fireEvent('embed');
		});
}catch(e){ test( e ) };
	},
	/**************************************************************************/
	/******************************** HANDLERS ********************************/
	comboList: function(el, atts){							/*** COMBO LIST ***/
		var that = this;
		$$('.comboList').forEach(function(cl){
			cl.evt = cl.addEvent('change', function(e){
				if( !this.value ) return;
				that.sendRequest('viewItem', atts, this.value);
			});
		});
	},
	bigTools: function(el, atts){
		var that = this, btns = {}
		var btnOn = 'bigToolEnabled';
		var btnOff = 'bigToolDisabled';
		function enable( uID ){
			this.hasClass( btnOff ) || this.addClass( btnOn );
			this.uID = uID || '';
		};
		function disable(){
			this.removeClass( btnOn );
			this.uID = '';
		};
		// Trip through all buttons to set event handlers and register each
		el.getElement('.bigTools').getElements('[btn]').forEach(function(btn){
			Object.append(btn, {enable: enable, disable: disable});
			btn.axn = btn.getAttribute('BTN');
			btn.addEvent('click', function(e){
				btn.hasClass(btnOn)
					&& that.sendRequest(btn.axn + 'Item', atts, btn.uID);
			});
			btns[btn.axn] = btn;		// Register this btn
		});
		// Enable/disable buttons, by code and globally for all at once
		el.enable = function(axn, uID){ btns[axn] && btns[axn].enable(uID); };
		el.disable = function( axn ){ btns[axn] && btns[axn].disable(); };
		el.allBtns = function(onOff, uID){
			Object.each(btns, function(btn){ btn[onOff] && btn[onOff](uID); });
		};
	},
	commonList: function (el, atts){					   /*** COMMON LIST ***/
		var that = this;
		// Mark listWrapper with this snippet's unique ID
		el.getElement('.listWrapper').id = 'tgt' + atts.params.group_uID;
		// Set initial state for bigTools snippet (and reset it to that state)
		this.resetBigTools(el, atts, 'create');
		// Enable column search and do first search without filters
		new ListSearch(el, atts).updateList();
		// SyncTitles applies to commonList, but is called by innerCommonList,
		// so it needs to be made available to innerCommonList on list update
		el.getElement('.listWrapper').ST = new SyncTitles(el, atts).sync();
	},
	innerCommonList: function(el, atts){
		var that = this;
		// innerCommonList is usually loaded within a list, so it might
		// need to sync titles width with it. If that's the case, SyncTitles
		// object should be found in one of the parents, so we search for it
		var oWrapper = el, ST = null;
		while( !ST && (oWrapper=oWrapper.parentNode) ) ST = oWrapper.ST;
		if( ST ) ST.sync();
		// See if we have a bigTools snippet attached, and store a reference
		var bigTools = that.groups[atts.params.group_uID]['bigTools'];
		// Add highlight effects to list's rows
		listRowsHighlight( el );
		// Add event handlers
		el.getElements('.innerListRow').forEach(function(row){
			var id = row.getAttribute('FOR');
			var selClass = 'selectedListRow';
			if( !id ) return;
			// Link to info page for each item row
			row.addEvent('click', function(e){
				el.getElements('.'+selClass).forEach(function(row){
					row.removeClass( selClass );
				});
				this.addClass(selClass);
				// Enable all available bigTools
				if( bigTools ) bigTools.el.allBtns('enable', id);
				if( !this.embeddedView ) hideEmbeddedView();
			});
			row.addEvent('dblclick', function(e){
				// Remove previous embeddedViews if found
				hideEmbeddedView();
				this.embeddedView = !this.embeddedView;
				if( !this.embeddedView ) return;
				// Create new embeddedView
				new Element('TD', {
					'class': 'embeddedView',
					'id': 'embed_'+atts.params.group_uID,
					'colspan': row.cells.length,
					events: {embed:function(){ row.scrollIntoView(true); }}
				})
				.inject(new Element('TR').inject(row, 'after'))
				.addEvent('embed', function(){ row.scrollIntoView(true); });
				// Request the embeddedView content
				var fixedParams = {filters:id, writeTo:'embed_'+atts.params.group_uID};
				var params = Object.append(atts.params, fixedParams);
				that.addSnippet('snp_viewItem', atts.code, params);
			});
			// Links for each row's btns
			row.getElement('.innerListTools').addEvent('click', function(e){
				var btn = e.stop().target.getAttribute('BTN');
				!btn || that.sendRequest(btn+'Item', atts, id);
			});
		});
		function hideEmbeddedView(){
			var prev = el.getElement('TD.embeddedView');
			if( prev ) prev.getParent('TR').dispose();
		}
	},
	createItem: function(el, atts, editting){							  /*** INFO ***/
		if( editting ){
			this.resetBigTools(el, atts, ['list', 'create']);
			var bigTools = this.groups[atts.params.group_uID]['bigTools'];
			if( bigTools ) bigTools.el.enable('view', atts.params.filters);
		}
		else{
			this.resetBigTools(el, atts, 'list');
		};
		var that = this;
		var frm = el.getElement('.snippet_createForm');
		var tbl = el.getElement('.snippet_createTable');
		var fields = frm.getElements('INPUT[name],SELECT,TEXTAREA');
		el.TTT = new SnippetToolTips( fields );
		frm.getElement('INPUT[type=button]').addEvent('click', function(){
			var filters = {};
			fields.forEach(function(fld){
				if( typeof(filters[fld.name]) !== 'undefined' ){
					if( filters[fld.name].push ){
						filters[fld.name].push( fld.value||'' );
					}
					else{
						filters[fld.name] = [filters[fld.name], fld.value||''];
					};
				}
				else{
					filters[fld.name] = fld.value||'';
				};
			});
			that.sendRequest(editting ? 'edit' : 'create', atts, filters);
		});
	},
	editItem: function(el, atts){									  /*** INFO ***/
		this.createItem(el, atts, true);
	},
	viewItem: function(el, atts){									  /*** INFO ***/
		var that = this;
		var id = atts.params.filters;
		// Enable tooltiptext in edittable fields
		el.TTT = new SnippetToolTips( el.getElements('.viewItemEditable') );
		// Set initial state for bigTools snippet (and reset it to that state)
		this.resetBigTools(el, atts, ['list', 'create', 'edit', 'delete'], atts.params.filters);
		var resetFieldOnEdit = function(){};
		el.getElements('.viewItemEditable').forEach(function(field){
			field.addEvent('mouseover', function(){ this.highlight('#f0f0e6', '#e0e0e6'); });
// Disable in-place edition for now (it's still half-way for some features)
			field.addEvent('click', function(){
				// Store current value and reset field
				var html = field.innerHTML;
				var color = field.getStyle('color');
				field.setStyle('color', 'white');
				var input = new Element('INPUT', {
					events: {
						click:function(e){ e.stop(); },
						enter:function(){ requestEdit(this.value); },
						focus:function(){ this.select(); }
					},
					type: 'text',
					value: html
				}).inject(field, 'top');
				new Element('IMG', {		// Confirm edition
					src: SNIPPET_IMAGES + '/buttons/edit.png',
					events: {click:function(e){ e.stop(); requestEdit(input.value); }},
					title: 'aceptar'
				}).inject(field, 'top');
				new Element('IMG', {		// Cancel edition
					src: SNIPPET_IMAGES + '/buttons/delete.png',
					events: {click:function(e){ resetFieldOnEdit(); e.stop(); }},
					title: 'cancelar'
				}).inject(field, 'top');
				var requestEdit = function( value ){
					var filters = {id: id, field: field.get('FOR'), value: value};
					that.sendRequest('editField', atts, filters);
				};
				input.focus();
				resetFieldOnEdit();				// Reset previous edition (if any)...
				resetFieldOnEdit = function(){	// ...and configure this field's edit reset
					field.setStyle('color', color);
					field.set('html', html);
					el.TTT.hide();
					resetFieldOnEdit = function(){};	// It's a one-time call only
				};
			});
		});
	},
	
	
	complexList: function(el, atts){
		$$('.complexList_group').forEach(function(grp){
			var hdr = grp.getElement('.complexList_groupHeader');
			var bdy = grp.getElement('.complexList_groupBody');
			var prvw = grp.getElement('.complexList_preview');
			var expd = grp.getElement('.complexList_expand').setStyle('display', 'block');
			hdr.addEvent('click', function(e){
				bdy.setStyle('display', 'block');
				expd.setStyle('display', 'none');
			});
			expd.addEvent('click', function(e){
				bdy.setStyle('display', 'block');
				expd.setStyle('display', 'none');
			});
			grp.getElements('.complexList_property').forEach(function(prop){
				var lnk = prop.getElement('A');
				if( lnk ) lnk.addEvent('click', function(){
					iniPreview(prvw, lnk.innerHTML);
				});
			});
		});
		function iniPreview(box, name){
			var html = 'Cargando ' + name + '...' +
				"<img class='listPreLoad' src='" + SNIPPET_IMAGES + "/timer.gif' />";
			box.set('html', html);
			box.setStyle('display', 'block');
		};
	},
	
	
	
	
	simpleList: function(el, atts){
/*
		var SimpleList = function( $list ){			// Simple List
			var that = this;
			var row4edit = $list.getElement('.addItemToSimpleList');
			this.inputs = row4edit.getElements('INPUT, SELECT');
			var editting = {};
			this.createItem = function(){
				var data = editting.id ? {SL_ID: editting.id} : {};
				that.inputs.forEach(function(input){ data[input.name] = input.value; });
				var func = 'xajax_create' + code.capitalize();
				if( window[func] ) window[func](data);
			};
			this.enableEditItem = function( id ){
				var tgt = that.selectRow( id );
				$list.getElement('.createItemText').innerHTML = 'Modificar';
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
				$list.getElement('.createItemText').innerHTML = 'Agregar';
				editting = {};
			};
		};
		$$('.simpleList').forEach(function($list){
			var SL = new SimpleList( $list );
			SL.inputs.forEach(function(input){
				input.addEvent('enter', function(){ $('SLcreateItem').fireEvent('click'); });
			});
			$list.getElements('.innerListRow').forEach(function(row){
				row.addEvent('mouseover', function(){ highLight(this); });
				row.addEvent('click', function(){ SL.enableEditItem( this.getAttribute('FOR') ); });
			});
			$list.getElement('.createItemText').onclick = SL.createItem;
			$list.getElements('.tblTools').forEach(function(btn){
				var id = btn.getAttribute('FOR');
				var axn = btn.getAttribute('AXN');
				var func = 'xajax_' + axn + code.capitalize();
				btn.addEvent('click', function(e){
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
					window[func]( id );
				});
			});
		});
*/
	}
};



/**
 * @overview: adds animation to lists' rows on mouseover
 * @arguments: Element:el[, string:fromColor[, string:toColor]]
 * @returns: the list passed as first argument
 * @notes: can call it with different params as many times as you wish
 *         it will destroy previous handler and create one with new params
 * @disclaimer: this tool is tied to AppTemplate's library 'Snippet', so
 *              it requires mootools, and rows are of class innerListRow
 */
function listRowsHighlight(el, from, to){
	el.getElements('.innerListRow').forEach(function(row){
		row.removeEvent('mouseover', row.ref);
		row.addEvent('mouseover', row.ref=function(){
			if( this.hasClass('selectedListRow') ) return;
			this.highlight(from||'#f0f0e6', to||'#e0e0e6');
		});
	});
	return el;
};


var SnippetToolTips = function( fields ){
	var that = this;
	// Register all enabled/valid fields in a private property
	var oTgts = {};
	fields.forEach(function(field){
		if( field.get('FOR') ) oTgts[field.get('FOR')] = field;
		else if( field.name ) oTgts[field.name] = field;
	});
	// Create tooltiptext box and inject it
	var ttb = new Element('DIV', {
		'class': 'snippetToolTip',
		events: {click: function(){ that.hide(); }}
	}).inject(document.body);
	// Showing tooltips
	this.show = function(code, msg){
		that.hide();
		if(!oTgts[code] || !msg) return that;
		var coord = oTgts[code].getCoordinates();
		ttb.setPosition({y:coord.top+coord.height+5, x:coord.left})
		   .set('html', msg)
		   .setStyle('display', 'block');
		oTgts[code].focus();
	};
	this.hide = function(){
		ttb.setStyle('display', 'none');
	};
}


/**
 * 
 */
function ListSearch(el, atts){
	/**************************************************************************/
	/********************************* TOOLS **********************************/
	var showError = SnippetPort.showError;
	var addSnippet = SnippetPort.addSnippet;
	// Global reference to self
	var that = el.ListSearch = this;
	// Define most used elements and vars as private properties
	var oBox = el.getElement('.listSearch');
	var oBoxLbl = oBox.getElement('SPAN');
	var oInput = oBox.getElement('INPUT');
	var oFields = [];
	var boxWidth = null;
	var innerSnippet = 'inner' + atts.snippet.capitalize();
	// Public methods
	this.show = function( btn ){
		that.hide();
		if( !btn ) return;
		oBoxLbl.innerHTML = btn.alt;
		if( !boxWidth ) boxWidth = oBox.setStyle('display', 'block').getWidth();
		var rightLimit = el.getPosition().x + el.getWidth() - boxWidth;
		var left = Math.min(rightLimit, Math.max(0, btn.getPosition().x - 100));
		var top = btn.getPosition().y + 62;
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
		// Append set filters, and tgt ID to parent's atts
		var params = {filters:filters||[], writeTo:'tgt'+atts.params.group_uID};
		addSnippet(innerSnippet, atts.code, Object.append(atts.params, params));
		return that;
	};
	// Add search buttons to each title field and set them up
	el.getElement('.listTitles').getElements('DIV').forEach(function(field, i){
		 // Only for used title fields
		if( !field.innerHTML ) return;
		// Get code for this column and text to display in the box
		var code = field.getAttribute('FOR');
		var filter = (code == '*') ? 'todos' : field.innerHTML.toLowerCase();
		// Create and insert one search image per title
		var img = new Element('IMG', {
			'pos': i,
			'code': code,
			'class': 'listSearchBtn',
			'src': SNIPPET_IMAGES + '/buttons/search.png',
			'alt': filter,
			'title': filter
		}).inject(field, 'bottom');
		// Style and attach event handler to each title field
		field.setStyle('cursor', 'pointer').addEvent('click', function(){
			that[(oBox.code == code) ? 'hide' : 'show']( img );
		}).setStyle('display', 'block');
		oFields[i] = field;
	});
	// Attach event handlers
	oBox.getElement('.CloseButton').addEvent('click', this.hide);
	oInput.addEvents({
		keyup: function(e){			// Input or escape
			if( e.key == 'esc' ) return that.hide();
			var newSrch = oInput.value.replace('*', '%');
			if( newSrch == oBox.lastSrch ) return;
			var aux = {};
			aux[oBox.code] = oBox.lastSrch = newSrch;
			that.updateList( aux );
			Snippet.resetBigTools(el, atts);
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
	var oTitles = oBox.getElements('DIV').setStyle('display', 'none');
	// No need to attempt to sync titles each time if there's no titles
	this.sync = !oTitles.length ? function(){} : function(){
		var oRow = el.getElement('.listWrapper').getElement('TR');
		// Store horizontal position and width of each cell...
		var posX = [];
		Array.from((oRow||{}).cells||[]).forEach(function(cell){
			posX.push( $(cell).getPosition().x );
		});
		// ...and apply that info to titles
		var startX = posX.length ? posX[0] : null;
		var availWidth = parseInt(oBox.getStyle('width'));
		var defWidth = availWidth / (oTitles.length||1) - 10;
		// If we have results, fix width of each title
		oRow && oTitles.forEach(function(oTtl, i){
			oTtl.setStyles({
				left: (posX[i] || (startX + availWidth)) - startX,
				width: posX[i+1] ? posX[i+1]-posX[i] : ''
			});
			oTitles.setStyle('display', '');
		});
		return that;
	};
	// Re-sync if page is resized or menu is hidden
	window.addEvent('resize', that.sync);
	window.addEvent('menutoggled', that.sync);
};