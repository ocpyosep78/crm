/**
 * Pending:
 *		- #comboList should rely only on Modules (currently it calls getPage)
 *		- 
 */


var Modules = {
	showError: function( msg ){
		return showStatus( msg );
	},
	atts: function(type, code, modifier, extra){
		this.type = type;
		this.code = code;
		this.modifier = modifier||null;
		this.extra = extra||null;
	},
	/**************************************************************************/
	/********************************* COMMON *********************************/
	/**************************************************************************/
	initialize: function(type, code, modifier, extra){
this.code = code;
this.modifier = modifier;
this.src = '';
		return Modules[type]
			? Modules[type].call(this, new this.atts(type, code, modifier, extra))
			: this.showError('Modules.js error: wrong type');
	},
	/**************************************************************************/
	/******************************** HANDLERS ********************************/
	/**************************************************************************/
	info: function( atts ){										  /*** INFO ***/
	},
	comboList: function( atts ){							/*** COMBO LIST ***/
		$$('.comboList').forEach(function(cl){
			cl.onchange = function(e){
				getPage(e, this.getAttribute('FOR') + 'Info', [this.value]);
			};
		});
	},
	commonList: function ( atts ){							   /*** COMMON LIST ***/
		// Enable column search and do a first search (without filters)
		Modules.columnSearch.enable();
		Modules.columnSearch.requestNewList( [] );
	},
	updateCommonList: function( atts ){
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
		var oTitlesBox = $list.getElement('#tableTitles');
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
	columnSearch: {
		/* Properties */
		Box: null,					/* Search box */
		Input: null,				/* Input field */
		CloseButton: null,			/* Button to close search tools */
		Buttons: [],				/* Collection of search buttons in the page */
		showing: null,				/* Currently shown search box */
		searchID: null,				/* Unique ID for each search request */
		cacheBox: null,				/* DOM box to contain returned results */
		lastSearch: null,			/* Last searched term */
		/* Methods */
		enable: function(){
			if( !this.ini() ) throw('missing TableSearch parameters');
			this.Buttons = $$('.tableColumnSearch');
			for( var i=0, att, btn ; btn=this.Buttons[i] ; i++ ){
				att = btn.getAttribute('FOR');
				btn.setAttribute('TableSearchCol', i);
				btn.addEvent('click', function(e){ TableSearch.present(e, this, att); } );
			};
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
		process: function(){
			/* Don't repeat search when keyup provoked no changes */
			var searchString = this.Input.value.replace('*', '%');
			if( this.lastSearch == searchString ) return;
			/* Build filter */
			var filter = {};
			var col = this.Buttons[this.showing].getAttribute('FOR');
			filter[col] = this.lastSearch = searchString;
			this.requestNewList( filter );
		},
		requestNewList: function( filter ){
			/* Pass the input and aditional info to registered xajax function */
			this.searchID = newSID().toString();
			var params = ['updateCommonList', Modules.code, Modules.modifier,
						  {uID: this.searchID, filters: filter, src: Modules.src}];
			xajax_ModulesAjaxCall.apply(window, params);
		},
		showResults: function( uID ){
			/* Make sure we're receiving the most recent request */
//			if( !$('listWrapper') || uID != this.searchID ) return;
			$('listWrapper').innerHTML = this.cacheBox.innerHTML;
			this.cacheBox.innerHTML = '';
		}
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



/*

Notes while working at home, forgot to push last changes so...

Some considerations for JS Modules object:

	- it needs to be able to record the state of more than one module element sharing the same namespace (meaning not in an iframe).
		solutions (brainstorm, no smarty thoughts here):
			1- make it a constructor instead of a single literal object
			2- save state of each object in the DOM
			3- use and abuse clossures and send atts around everywhere they need to go
		all of them have pros and cons... let's see:
			1- we'd still need to save the created object somehow, and tag it by a unique ID. This is not too different from tagging atts, except it duplicates things needlessly. However, it might be cleaner once the tagging is sorted, then every object will have its private state unchanged.
			2- which elements need to run initialization more than once? commonLists for now, maybe simpleLists in the future (pagination, sorting)
			   which elements would not see well to be initialized twice? in general, all if there's event handlers attached twice
		
		Let's then explore the following (yeah, I know last paragraph is mixed and left unfinished, so what).
		
		
		1. PHP#ModulesBase calls automatically and dynamically JS's Modules#initialize(type, code, modifier, params)
		2. JS:Modules#initialize creates var atts = {type:type, code:code, modifier:modifier||'', params:params||{}}
		3. It checks that this[type] exists and is a function, calls it if it does or raises an error if it doesn't
		   There's no point in storing attributes globally when they're not global (change for different elements)
		4. Each element then has it's own function, that I'll call that element's Handler.
		   Handlers do initialize not the element we want but any element of that kind (identified by className)
		   For that reason, it's imperative to get rid of ids in their templates. Classes should be used instead.
		   For example, if we attempt to initialize a commonList (its skeleton, the list frame), it'll go like:
		   
		   commonList: function( atts ){
               $$('.tableOutterWrapper').forEach(function(ttl){
			       if( ttl.initialized ) return;	// Don't initialize it twice, there's no need to
			       *add event handlers to create button and other tools that might be added to commonLists*
				   *add event handlers to search tools in particular (event if titles are hidden from the start)*
				   ttl.initialized = true;			// This list won't be initialized again
				   *request the first list to be loaded, without filters*
			   });
		   }
		  
STOP! so I changed my mind... if I send the params like this, how would I know which list they're for???

So what if we store attributes (type, code, modifier, params) in the HTML of that element? All elements have HTML and all of them are built automatically. Meaning there's just so many templates, and -better yet- all are called by the same central method (ModulesBase#fetch). What if...


		protected function fetch($name, $data=array()){
		
			$name = preg_replace('/\.tpl$/', '', $name);
		
			foreach( $data + $this->vars as $k => $v ) $this->TemplateEngine->assign($k, $v);
			
			if( !is_file(MODULES_TEMPLATES_PATH."{$name}.tpl") ) $name = '404';
			
			********** ADDED LINES ***********
			$this->assign('name', $name);
			return $this->TemplateEngine->fetch( 'global.tpl' );
		
			********** DISCARDED LINE ***********
			return $this->TemplateEngine->fetch( "{$name}.tpl" );
		
		}

For the global template see app/core/lib/Modules/static/templates/global.tpl
$params is not registered in the template yet. It should be, as it's proven to be very helpfull. It shouldn't be treated different than code or type. Except, of course, that it should be serialized (toJson method).

The most important change here is that all elements will be wrapped inside a div, with all of those wrappers sharing a class name. This is very practical when it comes to searching the DOM looking for a particular kind of element. I.e.:

	$$('.Modules_Element.Wrapper_of_Type_'+type) would give us all elements of type 'type' (gotta test IE...).
	
Moreover, we can use this same DIV to store whether that element is initialized already (some elements like updateCommonList could ignore it, but that's irrelevant, it costs nothing to keep track automatically and it could prove usefull in the future). So what if... centralized method JS:Modules#initialize already handles all this?!! Let's see:

	initialize: function( type ){
		var Elements = [], that = this;
		// Collect uninitialized elements of type 'type', ignore the rest
		$$('.Modules_Element.Wrapper_of_Type_'+type).forEach(function(dom){
			if( dom.initialized ) return;
			dom.initialized = true;
			dom.Atts = {type:type};
			['code', 'modifier', 'params'].forEach(function(att){
				var attDOM = dom.getElement('[@name='+att+']');
				if( attDOM ) dom.Atts[att] = attDOM.value;
				else return dom.Atts = null; // Unexpected error, abort 
			});
			// Remember params came as a JSON string
			dom.Atts.params = eval( dom.Atts.params );
			Elements.push( {dom:dom, atts:dom.Atts} );
		});
		// We call the handler method on each element
		Elements.forEach(function(el){
			that[type].call(that, el.dom, el.atts);
		});
	},
	someType(domEl, Atts){
		// Here, this points to Modules, el to the dom common wrapper
		// atts is an object with properties type, code, modifier, params
		// Attributes are stored also in the dom element (el.Atts)
	}

I can't think of any scenario where Elements would find more than one item, and it would end up empty only if there was an unexpected error (that I can't think of either). But, if it does find none, nothing happens (Handler won't be even called), and if it finds more than one, let it be happy. There's no possible conflicts or namespace crashes here: the Handler needs to use domEl and Atts in closures, and external methods can always go back to the dom element for persistence or re-getting atts from it.
	
Side Note: This would represent a challenge for styling, because a div surrounding each element is different from what we have now. The rimbombant class given to it, 'Modules_Wrapper_of_Type_{$name}' can be used in special cases to add float or other effects to a particular kind of element (and maybe replace former wrappers, like in updateCommonList elements).

Search tools for commonList could be a challenge, as they currently fall a long way off-limit of the element's area. A better solution would be maybe to make it appear flying in the middle, and keep the results when closed. Then adding a reset filters button would do the trick (it's about time I add a global search -by all columns at once-, while I'm on it...).
*/