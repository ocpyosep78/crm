var Modules = {
	initializeList: function (code, modifier, src){
		// Make sure the list exists (listFrame, named {code}List)
		var $list = $('listWrapper'), $titles = $('tableTitles');
		if( !$list || !$titles ) return raise('missing required elements');
		if( !$list.update ) $list.update = function(){
			fixTableHeader($titles, $$('.listTable')[0]);
			$$('.listRows').forEach(function(row){
				row.addEvent('mouseover', function(){ highLight(this); });
				if( row.getAttribute('FOR') ){
					row.addEvent('click', function(e){
						getPage(e, code + 'Info', [row.getAttribute('FOR')]);
					});
				};
			});
			$$('.tblTools').forEach(function(tool){
				var axn = tool.getAttribute('AXN');
				var id = tool.getAttribute('FOR');
				tool.addEvent('click', function(e){
					e.stop();
					switch( axn ){
						case 'delete':
							if( confirm('¿Realmente desea eliminar este elemento?') ){
								window['xajax_delete' + code.capitalize()](id, modifier);
							};
							break;
						case 'block':
							if( confirm('¿Realmente desea bloquear este elemento?') ){
								window['xajax_block' + code.capitalize()](id, modifier);
							};
							break;
						default: 
							getPage(e, axn + code.capitalize(), [id, modifier]);
							break;
					};
				});
			});
		};
		TableSearch.enableSearch(code, modifier, src||'');		/* Prepare search tools */
	},
	initializeSimpleList: function(code, modifier){
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
	},
	enableComboList: function(){
		$$('.comboList').forEach(function(cl){
			cl.addEvent('change', function(e){
				getPage(e, this.getAttribute('FOR') + 'Info', [this.value]);
			});
		});
	},
	TableSearch: {
		/* Properties */
		Box: null,					/* Search box */
		Input: null,				/* Input field */
		CloseButton: null,			/* Button to close search tools */
		Buttons: [],				/* Collection of search buttons in the page */
		funcAtts: [],				/* Attributes to pass on through Xajax */
		showing: null,				/* Currently shown search box */
		searchID: null,				/* Unique ID for each search request */
		cacheBox: null,				/* DOM box to contain returned results */
		lastSearch: null,			/* Last searched term */
		/* Methods */
		enableSearch: function(){
			if( !this.ini() ) throw('missing TableSearch parameters');
			this.Buttons = $$('.tableColumnSearch');
			this.populateList( arguments );
			for( var i=0, att, btn ; btn=this.Buttons[i] ; i++ ){
				att = btn.getAttribute('FOR');
				btn.setAttribute('TableSearchCol', i);
				btn.addEvent('click', function(e){ TableSearch.present(e, this, att); } );
			};
			/* Do first search (unfiltered) */
			this.process( true );
		},
		ini: function(){
			var that = this;
			var boxes = $$('.TableSearchBoxes')||[];
			if( boxes.length > 1 ) document.body.removeChild(boxes[1]);
			this.Box = $('TableSearchBox');
			this.Input = $('TableSearchInput');
			this.CloseButton = $('TableSearchCloseButton');
			this.Input.addEvent('keyup', function(e){ that.process(e); });
			this.CloseButton.addEvent('click', function(){ that.hideBox.apply(that); });
			this.createCacheBox();
			this.showing = -1;
			return this.Box && this.Input && BODY.appendChild( this.Box );
		},
		createCacheBox: function(){
			if( $('TableSearchCache') ) return;
			var tmp = this.cacheBox = $(document.createElement('DIV')).setStyle('display', 'none');
			tmp.id = 'TableSearchCache';
			BODY.appendChild( tmp );
		},
		populateList: function( args ){
			this.funcAtts = [];
			for( var i=0, arg ; arg=args[i] ; i++ ) this.funcAtts.push( arg );
		},
		present: function(e, obj, att){
			if( this.showing == obj.getAttribute('TableSearchCol') ) return this.hideBox();
			this.hideBox();
			this.showBox(e, obj);
		},
		hideBox: function(){
			this.Box.style.display = 'none';
			this.Input.value = '';
			if( this.showing >= 0 ) this.process( true );
			this.showing = -1;
		},
		showBox: function(e, tgt){
			this.Box.setStyle('left', e.page.x - 100);
			this.Box.setStyle('top', e.page.y + 48);
			this.Box.setStyle('display', 'block');
			this.showing = tgt.getAttribute('TableSearchCol');
			this.Box.getElement('SPAN').innerHTML = this.Buttons[this.showing].alt;
			$('TableSearchInput').focus();
		},
		process: function( clear ){
			/* Don't repeat search when keyup provoked no changes */
			var searchString = this.Input.value.replace('*', '%');
			if( this.lastSearch == searchString ) return;
			/* Build filter */
			var filter = {};
			if( clear !== true ){
				var col = this.Buttons[this.showing].getAttribute('FOR');
				filter[col] = this.lastSearch = searchString;
			};
			/* Pass the input and aditional info to registered xajax function */
			var params = [this.searchID=newSID().toString()].concat(filter, this.funcAtts);
			xajax_updateList.apply(window, params);
		},
		showResults: function( uID ){
			/* Make sure we're receiving the most recent request */
			if( !$('listWrapper') || uID != this.searchID ) return;
			$('listWrapper').innerHTML = this.cacheBox.innerHTML;
			this.cacheBox.innerHTML = '';
		}
	},
	fixTableHeader: function( oTitlesBox, oTable, cached ){
		if( !arguments.length && typeof(arguments.callee.sets) == 'object' ){
			for( var i=0, set ; set=arguments.callee.sets[i] ; i++ ){
				fixTableHeader(set['titles'], set['table'], true);
			};
			return;
		};
		if( !arguments.callee.sets ) arguments.callee.sets = [];
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
	}
};