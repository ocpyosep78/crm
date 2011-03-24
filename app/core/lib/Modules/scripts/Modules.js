/* Pending: #comboList should rely only on Modules (currently it calls getPage) */

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
			eval('dom.Atts.params = ' + dom.Atts.params);
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
try{
		el.ListSearch = new ListSearch(el, atts);
		el.ListSearch.updateList();
}catch(e){ test(e); };
	},
	updateCommonList: function( atts ){
		return test( atts );
try{
		Modules.fixTableHeader( $('listWrapper') );
		Modules.columnSearch.showResults( atts.uID );
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
}catch(e){ alert(e); };
	},
	/**************************************************************************/
	/***************************** AUXILIARY TOOLS ****************************/
	/**************************************************************************/
	fixTableHeader: function( $list ){
		var oTitlesBox = $list.getElementById('tableTitles');
		var oTable = $list.getElement('.listTable');
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
	},
	simpleList: function(code, modifier){
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
	// Save reference to this object as closure and within main dom element
	var that = this;
	// Define most used elements as private properties
	var oBox = el.getElement('.listSearch');
	var oBoxLbl = oBox.getElement('SPAN');
	var oInput = oBox.getElement('INPUT');
	var oList = el.getElement('.listWrapper');
	// Add search buttons to each title field
	el.getElement('.listTitles').getElements('DIV').forEach(function(field){
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
	// Create cache box
	var oCache = new Element('DIV', {style:'display:none'}).inject(document.body);
	// Private methods
	function process(){
		var newSrch = oInput.value.replace('*', '%');
		if( newSrch != process.lastSrch ){
			var aux = {};
			aux[oBox.code] = lastSrch = newSrch;
			that.updateList( aux );
		};
		process.lastSrch = newSrch;
	};
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
	};
	this.updateList = function( filters ){
		var type = 'update' + atts.type.capitalize();
		var uID = newSID().toString();
		oList.id = 'listWrapper_' + uID;
		var params = {uID:uID, filters:filters||[], src:atts.params.src||''};
		xajax_ModulesAjaxCall(type, atts.code, atts.modifier, params);
	};
	// Attach event handlers
	oInput.addEvent('escape', this.hide);
	oInput.addEvent('keyup', process);
	oBox.getElement('.CloseButton').addEvent('click', this.hide);
};