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
			'amount': row.find('input.quoteAmount'),
			'name': row.find('input.productName'),
			'suggest': row.find('div.suggestDIV'),
			'price': row.find('input.quotePrice'),
			'subTotal': row.find('TD.quoteSub'),
			'tax': row.find('TD.quoteTax'),
			'total': row.find('TD.quoteTotal')
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
		table: J('table.quotesTable')[0],
		basicLine: J('tr.quoteBasicLine')[0],
		selected: null,
		selectRow: function(row){
			if( this.selected ){
				this.selected.removeClass('rowSelected');
				if( this.selected.pos != row.pos ) this.acceptInput( this.selected.pos );
			};
			(this.selected = row).addClass('rowSelected');
		},
		addRowEvents: function(oRow){
			var that = this;
			var map = oRow.map;
			// Rapid list of events to catch
			map.name.keydown(keyDownOnName);
			map.name.keyup(keyUpOnName);
			map.amount.keyup(keyUpOnAmount);
//			map.price.keyup(keyUpOnPrice);
			map.amount.onfocus = map.price.onfocus = map.name.onfocus = focused;
			map.price.enter(enterOnPrice);
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
				if ((J.inArray(e.code, [91, 92, 93]))
				 || ((e.code > 111) && (e.code < 187))
				 || (!J.inArray(e.code, [8, 32, 46, 27]) && (e.code < 48))
				 || ((e.code > 191) && !J.inArray(e.code, [219, 220, 221]))
				 || (!J.inArray(e.key, ['space', 'backspace', 'delete', 'esc']) && (e.key.length != 1))) {
					return;
				}
				if (this.val().trim() && e.which != 27) {
					requestSuggest(this.val().trim());
				} else {
					Suggest.destroyList();
					map.suggest.html('');
					if (e.which == 27) {
						requestSuggest('');
						this.val('');
					}
				}
			}

			function keyUpOnAmount(e) {
				if (e.which == 13) {
					enterOnAmount();
				} else if (this.val()) {
					that.fixRowVals(oRow);
				}
			}

			function keyUpOnPrice() {
//				that.fixRowVals( oRow );
			}

			// Auxiliary functions (still holding closure vars)
			function enterOnAmount() {
				this.val() || this.val(1);
				map.name.focus();
			}

			function enterOnName() {
				that.acceptInput(oRow.pos);
				map.price.focus();
			}

			// Auxiliary functions (still holding closure vars)
			function enterOnPrice() {
				if (Rows[oRow.pos+1]) {
					Rows[oRow.pos+1].map.name.focus();
				}
			}

			function requestSuggest(txt) {
				map.suggest.html('');
				map.suggest.addClass('waiting');
				Suggest.request(txt, oRow.pos);
			}
		},

		showSuggest: function(data, pos) {
			var row = Rows[pos];
			row.data = data || {};
			row.map.suggest.removeClass('waiting');
			row.map.suggest.html(data.name || '(sin resultados)');
			row.map.price.val(parseFloat(data.price) || 0);
			if( data.amount ) row.map.amount.val(data.amount);
			else row.data.amount = parseInt(row.map.amount.val()) || 1;
			this.fixRowVals( row );
			return this;
		},
		acceptInput: function( pos ){
			Rows.newLines();	/* Adds new rows as needed */
			var oRow = Rows[pos];
			// See if user deleted entry, to remove it from data too
			if( oRow.data.accepted && oRow.map.name.val() == '' ) oRow.data = {};
			var name = oRow.data.name || '';
			oRow.map.suggest.html('');
			if( name ){
				oRow.map.name.val(name);
				oRow.data.accepted = true;
			};
			Suggest.destroyList();
			this.fixRowVals( oRow );
		},
		fixRowVals: function( row ){
			var map = row.map, data = row.data;
			data.price = parseFloat(map.price.val()) || data.price || 0;
			map.amount.val(parseInt(map.amount.val()) || 1);
			data.amount = map.amount.val();
			map.price.val(data.price.toFixed(2));
			var subTotal = (data.price * data.amount).toFixed(2);
			map.subTotal.html(subTotal);
			map.tax.html((subTotal * taxes).toFixed(2));
			map.total.html((subTotal * (taxes + 1)).toFixed(2));
			this.fixTotals();
		},
		fixTotals: function(){
			var tSubTotal = 0, tTax, tTotal;
			for( var i=0, oRow ; oRow=Rows[i] ; i++ ){
				if( !oRow.data || !oRow.data.amount ) continue;
				tSubTotal += parseFloat(oRow.data.amount * oRow.data.price) || 0;
			};
			J('#tSubTotal').html(tSubTotal.toFixed(2));
			J('#tTax')     .html((tSubTotal * taxes).toFixed(2));
			J('#tTotal')   .html((tSubTotal * (taxes + 1)).toFixed(2));
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
			var that = this;
			var pos = this.req.pos;
			J.each(this.req.list, function(line, i){
				var fn = (function(i){ return function(){
					Table.showSuggest(line, pos).acceptInput(pos);
				}})(i);
				J('<a />', {'href': 'javascript:void(0);'})
					.click(fn)
					.append(J('<div />').html(line.name))
					.appendTo(J('#suggestList'));
			});
			var coords = Rows[pos].map.name.position();
			J('#listBox').css({'top':coords.top+22, 'left':coords.left-1}).show();
		},
		pickOneResult: function(){	/* Keeps current result if it's in the list */
			var req = this.req;
			var i = req.list.length || 1;
			do{	req.selected = --i;
			}while( i && req.list[i] && req.list[i].id != req.product );
			return req.list[i] || {};
		},
		hideList: function(){
			J('#listBox').hide();
			J('#suggestList').html('');	/* Clear results */
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
			req.product = req.list[req.selected].id;
			J('#listBox a.selected').removeClass('selected');
			var currSelected = J('#listBox a')[req.selected];
			currSelected.addClass('selected');
			if (currSelected.parent().scrollTop() < currSelected.get(0).offsetTop){
				currSelected.get(0).scrollIntoView(false);
			};
		}
	};

	Rows.batchAdd( Data );		/* If there is data to initialize table, insert it (edit) */
	Rows.newLines();			/* Adds new rows as needed */

/* Outside the table ( buttons ) */

	// id_estimate will be empty if we're creating a new estimate/quote
	var id_estimate = J('#hdn_id_estimate').val() || '';

	J('#btn_save').click(function(){
		if (!J('#param_estimate').val()) {
			return say('Debe escribir un nombre válido para guardar un presupuesto o cotización.');
		}

		// Get data from the table, and estimate's params
		var Data = Rows.getData();
		var Params = {
			'id_estimate': id_estimate,
			'estimate': J('#param_estimate').val(),
			'orderNumber': J('#param_orderNumber').val() || '',
			'id_customer': J('#param_id_customer').val() || '',
			'id_system': J('#param_id_system').val() || '',
			'pack': J('#hdn_pack').val() || ''
		};

		// Ask for confirmation if table is empty
		if (!Data.length && !confirm('La lista está vacía. ¿Desea guardarla de todos modos?')) {
			return;
		}

		if (!Params.orderNumber || !Params.id_customer || !Params.id_system) {
			alert('Faltan datos requeridos para crear un Presupuesto.\n' +
				  'La lista será guardada como Cotización.');
		};

		xajax_saveEstimate(Params, Data, id_estimate);
	});

	J('#param_id_system').change(function(){
		this.val() && J('#img_system')._src('app/images/systems/'+this.val()+'.png');
	});
};

function ini_editEstimates(){
	ini_createEstimates( window.estimateDetail );
};

function ini_estimatesInfo(){
	var id = J('#hdn_id_estimate').val();

	J('#btn_edit').click(function(e){ getPage(e, 'editEstimates', [id]); });
	J('#btn_print').click(function(){ xajax_printQuote(id); });
	J('#btn_design').click(function(e){ getPage(e, 'installPlan', [id]); });
	J('#btn_exportPDF').click(function(e){
		return getPage(e, 'estimatePDF', [id]); // TODO
		var height = (parseInt(screen.availHeight) * .90) + 'px';
		var width = (parseInt(screen.availWidth) * .90) + 'px';
		var atts = 'location=NO,menubar=NO,toolbar=NO,height='+ height +',width='+ width;
		window.open('app/export/pdf/estimate.php?id=' + id, 'Presupuesto', atts);
	});

	window.showWarnings = function(){}; // TODO
};

function printQuote(){
	var content = J('#tmpDivToPrint').html();
	J('#tmpDivToPrint').html('');
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

function ini_installPlan(id) {
	J.forms('plan').submit(function(){
		return xajax_addEntryToPlan(xajax.getFormValues(this)) & false;
	});
	J('#installPlan img').parent().click(function(){
		xajax_removeEntryFromPlan(J(this)._for());
	});
	J('#backToEstimateInfo').click(function(){
		getPage('estimatesInfo', [id]);
	});
};

function ini_estimatePDF(id) {
	J('#backToEstimateInfo').click(function(){
		getPage('estimatesInfo', [id]);
	});
	// When clicking on Print button, a temporary iframe is created
	J('#printEstimatePDF').click(function(){
		var src = J('#estimatePDF')._src + '&printer&validated';

		J('#printFra').remove();
		J('<iframe />', {'id'  : 'printFra',
		                 'name': 'printFra',
		                 'src' : src})
			.load(function(){ J('#printFra').get(0).print(); })
			.hide()
			.appendTo('body');
	});
	// Show Print button only when the estimate is finally shown (after validation)
	J('#estimatePDF').load(function(){
		if( window.frames.estimatePDF.location.href.indexOf('validated') !== -1 ){
			J('#printEstimatePDF').hide();
		};
	});
}

function ini_createEstimates_pack() {
	J('#createEstimatesPack').click(function(){
		var name = J('#createEstimatesPack_name').val();
		var cust = J('#createEstimatesPack_id_customer').val();

		if (!name || !cust) {
			return alert('Debe llenar todos los campos para continuar.');
		}

		xajax_createEstimates_pack({name: name, id_customer: cust});
	});
}

function ini_editEstimates_pack(id) {
	ini_estimates_packInfo(id);
}

function ini_estimates_packInfo(id) {
	var pack = J('#estimates_pack_tools_add')._for();
	J('#estimates_pack_tools_add').change(function(){
		var estimate = J('#estimates_pack_tools_add').val();
		estimate && xajax_addEstimate2Pack({pack: pack, id_estimate: estimate});
	});
	J('#createEstimate').click(function(){
		getPage('createEstimates', ['', id]);
	});
}