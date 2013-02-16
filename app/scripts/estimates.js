function ini_estimates(){};
function ini_quotes(){};

function ini_createEstimates( Data ){
	
/*

Rows: hash of Row objects, plus common logical methods
Row: object representing a row in the table (logical)
	links to DOM elements
Data: data object keeps track of the data contained in a row
Table: handles methods related to the DOM representation of Rows
Suggest: all ajax related to suggest feature are handled by Suggest

In summary:

	(user) Input / Output					Table
	(ajax) Input / Communication			Suggest
	(logic) Data handling					Data
	(logic) Data organization				Rows
	(mixed) Link DOM <=> Logic				Row

Premises:

	- It must be possible (and easy) to build a complete table
		reflecting logic data contained in Rows hash
	- It must be possible (and easy) to feed Rows hash with info
		taken from the DOM table
	- No ajax related methods must be known to others than Suggest
	- No data must be known to Row and Rows
	- No DOM mapping must exist outside the Row objects
	- Table knows no logic, except recognizing input (events)
	- Suggest doesn't know what it's handling
	- All DOM changes and listening are done by Table (exclusive)
	
	
Object Rows: array with custom methods
	It keeps an ordered list of Row objects, and provides usefull
	common methods for them to use (mostly requiring pos -index).

	
***** Rows.push *****			@params: DOM[TR]:row, Data:data
- Adds Row objects to the array
- Optionally, accepts Data object to link to the Row
- Passing a DOM row (TR) is optional. One will be created if not passed
- Links main own methods to the Row object being added

***** Rows.createRow *****
- Creates a DOM row in the estimates table, and returns it

						
*/
/*******************************************************/
/*************** O B J E C T :   R O W S ***************/
/*******************************************************/
	var Rows = [];
	Rows.push = function( row ){		// Adding existing DOM rows
		var pos = Rows.length;
		var oRow = Rows[pos] = new Row(row, pos);
	};
	Rows.newLines = function(){
		while( Rows.length < 2 || Rows[Rows.length-1].data.id ){
			Rows.push();
		}
	};
	Rows.getData = function(){
		var Data = [];
		for( var i=0, row ; row=Rows[i] ; i++ ){
			if( row.data && row.data.accepted ) Data.push(row.data);
		};
		return Data;
	};
	Rows.batchAdd = function( Data ){
		if( !Data ) return;
		Rows.push();
		for( var x in Data ) if( Data.hasOwnProperty(x) ){
			var pos = Rows.length-1;
			Table.showSuggest(Data[x], pos);
			Table.acceptInput( pos );
		};
	};
	
/*******************************************************/
/*********** C O N S T R U C T O R :   R O W ***********/
/*******************************************************/
	function Row(row, pos){
		row = row || Table.createRow();
		row.map = this.map = {
			'row': row,
			'amount': row.getElement('INPUT.quoteAmount'),
			'name': row.getElement('INPUT.productName'),
			'suggest': row.getElement('DIV.suggestDIV'),
			'price': row.getElement('INPUT.quotePrice'),
			'subTotal': row.getElement('TD.quoteSub'),
			'tax': row.getElement('TD.quoteTax'),
			'total': row.getElement('TD.quoteTotal')
		};
		this.pos = pos;
		this.data = {};
		for( var x in this.map ){
			if( this.map.hasOwnProperty(x) ){
				this.map[x].pos = pos;
			};
		};
		Table.addRowEvents( this );
	};
	
/*******************************************************/
/************** O B J E C T :   T A B L E **************/
/*******************************************************/
	Table = {
		table: $$('TABLE.quotesTable')[0],
		basicLine: $$('TR.quoteBasicLine')[0],
		selected: null,
		createRow: function(){
			var row = $(this.basicLine.cloneNode(true));
			row.removeClass('quoteBasicLine');
			this.basicLine.parentNode.insertBefore(row, this.basicLine);
			return row;
		},
		selectRow: function( row ){
			if( this.selected ){
				this.selected.removeClass('rowSelected');
				if( this.selected.pos != row.pos ) this.acceptInput( this.selected.pos );
			};
			(this.selected = row).addClass('rowSelected');
		},
		addRowEvents: function( oRow ){
			var that = this;
			var map = oRow.map;
			// Rapid list of events to catch
			map.name.addEvent('keydown', keyDownOnName);
			map.name.addEvent('keyup', keyUpOnName);
			map.amount.addEvent('keyup', keyUpOnAmount);
//			map.price.addEvent('keyup', keyUpOnPrice);
			map.amount.onfocus = map.price.onfocus = map.name.onfocus = focused;
			map.price.addEvent('enter', enterOnPrice);
			// Event handlers
			function focused(){
				that.selectRow( map.row );
				that.fixRowVals( oRow );
			};
			function keyDownOnName( e ){	// Special keys only
				switch( e.key ){
					case 'down':	Suggest.navigate( +1 ); break;
					case 'up':		Suggest.navigate( -1 ); break;
					case 'enter':	enterOnName(); break;
					default: return true;
				};
				e.preventDefault();
			};
			function keyUpOnName( e ){		// Non-special chars
				if( in_array(e.code, [91, 92, 93]) || (e.code > 111 && e.code < 187) ) return;
				if( !in_array(e.code, [8, 32, 46, 27]) && e.code < 48 ) return;
				if( e.code > 191 && !in_array(e.code, [219, 220, 221]) ) return;
				if( !in_array(e.key, ['space', 'backspace', 'delete', 'esc']) && e.key.length != 1 ) return;
				if(this.value.trim() && e.code != 27) requestSuggest( this.value.trim() );
				else{
					Suggest.destroyList();
					map.suggest.innerHTML = '';
					if( e.key == 'esc' ){
						requestSuggest('');
						this.value = '';
					};
				};
			};
			function keyUpOnAmount( e ){
				if( e.key == 'enter' ) enterOnAmount();
				else if( this.value ) that.fixRowVals( oRow );
			};
			function keyUpOnPrice( e ){
//				that.fixRowVals( oRow );
			};
			// Auxiliary functions (still holding closure vars)
			function enterOnAmount( e ){
				if( !this.value ) this.value = 1;
				map.name.focus();
			};
			function enterOnName(){
				that.acceptInput( oRow.pos );
				map.price.focus();
			};
			// Auxiliary functions (still holding closure vars)
			function enterOnPrice(){
				if( Rows[oRow.pos+1] ) Rows[oRow.pos+1].map.name.focus();
			};
			function requestSuggest( txt ){
				map.suggest.innerHTML = '';
				map.suggest.addClass('waiting');
				Suggest.request(txt, oRow.pos);
			};
		},
		showSuggest: function(data, pos){
			var row = Rows[pos];
			row.data = data || {};
			row.map.suggest.removeClass('waiting');
			row.map.suggest.innerHTML = data.name || '(sin resultados)';
			row.map.price.value = parseFloat(data.price) || 0;
			if( data.amount ) row.map.amount.value = data.amount;
			else row.data.amount = parseInt(row.map.amount.value) || 1;
			this.fixRowVals( row );
			return this;
		},
		acceptInput: function( pos ){
			Rows.newLines();	/* Adds new rows as needed */
			var oRow = Rows[pos];
			// See if user deleted entry, to remove it from data too
			if( oRow.data.accepted && oRow.map.name.value == '' ) oRow.data = {};
			var name = oRow.data.name || '';
			oRow.map.suggest.innerHTML = '';
			if( name ){
				oRow.map.name.value = name;
				oRow.data.accepted = true;
			};
			Suggest.destroyList();
			this.fixRowVals( oRow );
		},
		fixRowVals: function( row ){
			var map = row.map, data = row.data;
			data.price = parseFloat(map.price.value) || data.price || 0;
			data.amount = map.amount.value = parseInt(map.amount.value) || 1;
			map.price.value = data.price.toFixed(2);
			var subTotal = map.subTotal.innerHTML = (data.price * data.amount).toFixed(2);
			map.tax.innerHTML = (subTotal * taxes).toFixed(2);
			map.total.innerHTML = (subTotal * (taxes + 1)).toFixed(2);
			this.fixTotals();
		},
		fixTotals: function(){
			var tSubTotal = 0, tTax, tTotal;
			for( var i=0, oRow ; oRow=Rows[i] ; i++ ){
				if( !oRow.data || !oRow.data.amount ) continue;
				tSubTotal += parseFloat(oRow.data.amount * oRow.data.price) || 0;
			};
			$('tSubTotal').innerHTML = tSubTotal.toFixed(2);
			$('tTax').innerHTML = (tSubTotal * taxes).toFixed(2);
			$('tTotal').innerHTML = (tSubTotal * (taxes + 1)).toFixed(2);
		}
	};
	
/*******************************************************/
/************ O B J E C T :   S U G G E S T ************/
/*******************************************************/
	/* Object to handle suggest lists dynamics */
	window.Suggest = {
		that: this,
		req: {},
		request: function(txt, pos){
			this.req.id = newSID();
			this.req.pos = pos;
			if( !txt ) this.processList([], this.req.id);
			else if( this.req.lastSearch != txt ){		/* Dont' repeat search */
				xajax_suggestProduct(txt, this.req.id);
			};
		},
		processList: function(list, reqID){
			if( this.req.id != reqID ) return;						/* Ignore outdated requests */
			this.req.list = list || [];								/* New resultset */
			this.hideList();										/* Hide previous list */
			Table.showSuggest(this.pickOneResult(), this.req.pos);	/* Set the right listID */
			if( this.req.list.length ){
				this.showList();	/* Show list if there is one */
				this.selectInList();
			};
		},
		showList: function(){
			var pos = this.req.pos;
			this.req.list.forEach(function(line, i){
				var A = document.createElement('A');
				A.innerHTML = '<div>' + line.name + '</div>';
				$('suggestList').appendChild( A );
				A.href = 'javascript:void(0);';		/* To style it in IE */
				A.onclick = (function(i){ return function(){
					Table.showSuggest(line, pos).acceptInput(pos);
				}})(i);
			}, this);
			var coords = Rows[pos].map.name.getPosition();
			$('listBox').setStyles({top:coords.y+22, left:coords.x-1, display:'block'});
		},
		pickOneResult: function(){	/* Keeps current result if it's in the list */
			var req = this.req;
			var i = req.list.length || 1;
			do{	req.selected = --i;
			}while( i && req.list[i] && req.list[i].id != req.product );
			return req.list[i] || {};
		},
		hideList: function(){
			$('listBox').setStyle('display', 'none');
			$('suggestList').innerHTML = '';	/* Clear results */
		},
		destroyList: function(){
			this.hideList();
			this.req.list = [];
		},
		navigate: function( step ){
			if( this.req.list[this.req.selected + step] ){
				Table.showSuggest(this.req.list[this.req.selected += step], this.req.pos);
			};
			this.selectInList();
		},
		selectInList: function(){
			var req = this.req;
			req.product = (req.list[req.selected]||{}).id;
			var prevSelected = $('listBox').getElement('A.selected');
			if( prevSelected ) prevSelected.removeClass('selected');
			var currSelected = $$('#listBox A')[req.selected];
			if( currSelected ){
				currSelected.addClass('selected');
				if( currSelected.parentNode.scrollTop < currSelected.offsetTop ){
					currSelected.scrollIntoView( false );
				};
			};
		}
	};
	
	Rows.batchAdd( Data );		/* If there is data to initialize table, insert it (edit) */
	Rows.newLines();			/* Adds new rows as needed */
	
/* Outside the table ( buttons ) */
	
	// id_estimate will be empty if we're creating a new estimate/quote
	var id_estimate = $('hdn_id_estimate').value || '';
	
	if( $('btn_save') ) $('btn_save').onclick = function(){
		if( !$('param_estimate').value ){
			return showStatus('Debe escribir un nombre válido para guardar un presupuesto o cotización.');
		};
		// Get data from the table, and estimate's params
		var Data = Rows.getData();
		var Params = {
			'id_estimate': id_estimate,
			'estimate': $('param_estimate').value,
			'orderNumber': $('param_orderNumber').value || '',
			'id_customer': $('param_id_customer').value || '',
			'id_system': $('param_id_system').value || '',
			'pack': $('hdn_pack').value || '',
		};
		// Ask for confirmation if table is empty
		if( !Data.length && !confirm('La lista está vacía. ¿Desea guardarla de todos modos?') ) return;
		if( !Params.orderNumber || !Params.id_customer || !Params.id_system ){
			alert('Faltan datos requeridos para crear un Presupuesto.\n' +
				  'La lista será guardada como Cotización.');
		};
		xajax_saveEstimate(Params, Data, id_estimate);
	};
	
	($('param_id_system')||{}).onchange = function(){
		if( !this.value ) return;
		$('img_system').src = 'app/images/systems/' + this.value + '.png';
	};
	
};

function ini_editEstimates(){
	ini_createEstimates( window.estimateDetail );
};

function ini_estimatesInfo(){
	var id = $('hdn_id_estimate').value;
	($('btn_edit')||$E).addEvent('click', function(e){ getPage(e, 'editEstimates', [id]); });
	($('btn_print')||$E).addEvent('click', function(){ xajax_printQuote( id ); });
	($('btn_design')||$E).addEvent('click', function(e){ getPage(e, 'installPlan', [id]); });
	($('btn_exportPDF')||$E).addEvent('click', function(e){
		return getPage(e, 'estimatePDF', [id]);
		var height = (parseInt(screen.availHeight) * .90) + 'px';
		var width = (parseInt(screen.availWidth) * .90) + 'px';
		var atts = 'location=NO,menubar=NO,toolbar=NO,height='+ height +',width='+ width;
		window.open('app/export/pdf/estimate.php?id=' + id, 'Presupuesto', atts);
	});
	window.showWarnings = function(){
		
	};
};



function printQuote(){
	var content = $('tmpDivToPrint').innerHTML;
	$('tmpDivToPrint').innerHTML = '';
	var ref = window.open('', 'printQuotePopup', 'height=500px, width=800px,left=10px,top=10px');
	ref.document.open();
	ref.document.write( "<link rel='stylesheet' type='text/css' href='app/styles/estimates.css'>" );
	ref.document.write( "<div class='printableEstimate'>" + content + '</div>' );
	ref.document.write( "<script type='text/javascript'>self.print();</script>" );
	ref.document.close();
	ref.blur();
	ref.focus();
	ref.opener = null;
};

function ini_installPlan( id ){
	var frm = $(document.forms.plan);
	frm.addEvent('submit', function(){
		xajax_addEntryToPlan(xajax.getFormValues(frm));
		return false;
	});
	$('installPlan').getElements('IMG').forEach(function(img){
		$(img.parentNode).addEvent('click', function(){
			xajax_removeEntryFromPlan( this.getAttribute('FOR') );
		});
	});
	$('backToEstimateInfo').addEvent('click', function(){
		getPage('estimatesInfo', [id]);
	});
};

function ini_estimatePDF( id ){
	$('backToEstimateInfo').addEvent('click', function(){
		getPage('estimatesInfo', [id]);
	});
	// When clicking on Print button, a temporary iframe is created
	$('printEstimatePDF').addEvent('click', function(){
		if( $('printFra') ) document.body.removeChild( $('printFra') );
		var printFra = document.createElement('IFRAME');
		printFra.name = printFra.id = 'printFra';
		$( document.body.appendChild(printFra) )
			.setStyle('visibility', 'hidden')
			.addEvent('load', function(){ window.frames.printFra.print(); })
			.src = $('estimatePDF').src + '&printer&validated';
	});
	// Show Print button only when the estimate is finally shown (after validation)
	$('estimatePDF').addEvent('load', function(){
		if( window.frames.estimatePDF.location.href.indexOf('validated') !== -1 ){
			$('printEstimatePDF').setStyle('display', 'block');
		};
	});
};

function ini_createEstimates_pack(){
	$('createEstimatesPack').addEvent('click', function(){
		var name = $('createEstimatesPack_name').value;
		var cust = $('createEstimatesPack_id_customer').value;
		if( name == '' || !cust ) return alert('Debe llenar todos los campos para continuar.');
		xajax_createEstimates_pack({name: name, id_customer: cust});
	});
};

function ini_editEstimates_pack( id ){ ini_estimates_packInfo(id); };
function ini_estimates_packInfo( id ){
	var pack = $('estimates_pack_tools_add').getAttribute('FOR');
	$('estimates_pack_tools_add').addEvent('change', function(){
		var estimate = $('estimates_pack_tools_add').value;
		if( estimate ) xajax_addEstimate2Pack( {pack: pack, id_estimate: estimate} );
	});
	$('createEstimate').addEvent('click', function(){
		getPage('createEstimates', ['', id]);
	});
};