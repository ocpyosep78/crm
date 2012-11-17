function ini_customers(type) {};

function ini_potentialCustomers() {};

function ini_customersInfo() {
	$('#editCustomers').click(function(e){
		getPage(e, 'editCustomers', [$(this)._for()]);
	});
};

function ini_createCustomers(isNotNew) {
	$('#potentialSubmit').click(function(){
		var action = isNotNew ? 'editCustomers' : 'createCustomers';
		xajaxSubmit($('#createCustomerForm'), action, true);
	});
};

function ini_editCustomers() {
	ini_createCustomers(true);
};

function ini_registerSales() {
	var frm = $.forms('frmOldSales');

	frm.setSeller = function(code){
		this.seller.val(code);
	};
	frm.id_customer.change(function(){
		frm.seller.val(0);
		silentXajax('setSeller', [this.val()]);
	});
	frm.submit(function(){
		xajax_registerSale(xajax.getFormValues(frm.get(0)));
	});
	frm.restart = function(){
		this.reset().find('[name="saleType"]:first').click();
	};

	/* Following code adjusts which element should be disabled depending on
		which type of sale it is (fields being a list of all depending fields) */
	var optionalFields = {	/* each type list includes depending fields to SHOW */
		fields: ['id_system', 'id_installer', 'technician', 'warranty'],
		system: ['id_system', 'id_installer', 'warranty'],
		product: ['warranty'],
		service: ['technician']
	};

	frm.find('[name="saleType"]').click(function(e){
		e.stopPropagation();

		$.each(optionalFields.fields, function(i, field){
			frm[field].attr('disabled', true);
		});
		$.each(optionalFields[$(this).val()], function(i, field){
			frm[field].attr('disabled', false);
		});
	}).parent().click(function(){
		$(this).find('[name="saleType"]').click();
	});

	frm.restart();
};