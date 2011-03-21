function ini_customers( type ){};
function ini_potentialCustomers(){};

function ini_customersInfo(){
	if( $('editCustomers') ) $('editCustomers').onclick = function(e){
		getPage(e, 'editCustomers', [this.getAttribute('for')]);
	};
};

function ini_createCustomers( isNotNew ){
	($('potentialSubmit')||{}).onclick = function(){
		xajaxSubmit($('createCustomerForm'), isNotNew ? 'editCustomers' : 'createCustomers', true);
	};
};

function ini_editCustomers(){
	ini_createCustomers( true );		/* Initialize like new customer but with a different prefix */
};

function ini_registerSales(){
	var frm = $(document.forms['frmOldSales']);
	for( var i=0, els=$(frm).getElements('INPUT,SELECT'), el ; el=els[i] ; i++ ){
		if( el.name && el.type != 'radio' ) newTip(el.name, el);
	};
	frm.setSeller = function( code ){
		selectOption(this['seller'], code||'', 'value');
	};
	$(frm['id_customer']).addEvent('change', function(){
		selectOption(frm['seller'], 0);
		silentXajax('setSeller', [this.value]);
	});
	frm.addEvent('submit', function(){
		xajax_registerSale( xajax.getFormValues(frm) );
	});
	/* Following code adjusts which element should be disabled depending on
		which type of sale it is (fields being a list of all depending fields) */
	var optionalFields = {	/* each type list includes depending fields to SHOW */
		fields: ['id_system', 'id_installer', 'technician', 'warranty'],
		system: ['id_system', 'id_installer', 'warranty'],
		product: ['warranty'],
		service: ['technician']
	};
	$$(frm.saleType).forEach(function(rad){
		rad.addEvent('click', function(){
			rad.checked = true;
			optionalFields.fields.forEach(function(f){ frm[f].disabled = true; });
			optionalFields[rad.value].forEach(function(f){ frm[f].disabled = false; });
		});
		$(rad.parentNode).addEvent('click', function(){ rad.click(); });
	});
	// I make it a property of the form to be able to call it through ajax
	frm.restart = function(){
		this.reset();
		$$(this.saleType)[0].click();
	};
	frm.restart();
};