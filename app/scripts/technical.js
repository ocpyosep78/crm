function ini_createTechVisits( data ){
	/* Show form as soon as picture is fully loaded */
	function showForm(){
		clearTimeout(to);
		$('#technicalForm').show();
		$('#tch_custNumber').focus();
	};
	var to = setTimeout(showForm, 2000);		/* Just in case pic loaded already (should not, but...) */
	$('#technicalFormBg').load(showForm);

	/* Collection of methods, attached to a DOM element (to be called through xajax) */
	var TechnicalForm = $('#technicalForm').get(0).handler = {
		frm: {},
		ss: {},
		ini: function(data){
			var that = this;
			var frm = this.frm = $.forms('frm_newTechVisit', 'tch_');

			frm.ifIncomplete.keyup(function(){
				frm.complete._checked(true);
			});

			frm.complete.mousedown(function(){
				var check = $(this);
				check._checked() && setTimeout(function(){
					check._checked(false);
				}, 100);
			});

			/* Attach event handlers to search boxes */
			$('.tchSearch').each(function(){
				var by = this._id().replace(/^tchSrch_/, '');

				this.click(function(){
					that.search.call(that, by);
				});

				frm[by].keydown(function(e){
					return (e.which != 13);
				});

				frm[by].enter(function(){
					that.search.call(that, by);
				});
			}, true);

            if (data.id_customer && !data.id_sale) {
                xajax_tchFormAcceptSale('', data.id_customer);
            } else if(data) {
                this.fillForm(data, !data.id_sale);
            }
		},
		/* data is sent as parameter if provided, otherwise the whole form is sent */
		search: function(by){
			this.frm[by] && xajax_tchFormSuggest(by, this.frm[by].val());
		},
		clearSuggest: function(){
			$('#tch_suggest').html('');
		},
		/* data is a JSON 2-dimensional object: first level is customers,
			second level is invoices (plus detail) */
		suggest: function(data){
			this.clearSuggest();

			if (typeof(data) != 'object') return;

			if (data.length > 30)
			{
				data = data.slice(0, 30);

				var msg = 'Listado Parcial (mostrando los primeros 30 resultados)';
				$('#tch_suggest').html("<div class='tch_s_notice'>" + msg + "</div>");
			}
			else if (!data.length)
			{
				var msg = 'No hay resultados que coincidan con su b�squeda';
				$('#tch_suggest').html("<div class='tch_s_empty'>" + msg + "</div>");
			}

			$.map(data, this.addCustomersSuggest);
		},
		addCustomersSuggest: function(data){	/* Builds the list of suggested invoices/sales/installs */
			var customer = data['customer'];
			var rows = data['rows'];

			$('#tch_suggest').html($('#tch_suggest').html() +
				"<div class='tch_s_customer'>Cliente " + data.customer + "</div>" +
				"<div class='tch_s_contact'>" +
				(data.contact ? 'Contacto: ' + data.contact + '<br />' : '') +
				"</div>" +
				"<div class='tch_s_row tch_s_noInvoice' cust='" + data.id_customer + "'>" +
				"Servicio T�cnico sin factura previa</div>");

			$.each(rows, function(){
				$('#tch_suggest').html($('#tch_suggest').html() +
					"<div class='tch_s_row' for='" + this.onSale + "'>" +
					"Factura: " + this.invoice +
					(this.system ? ' (' + this.system + ")" : '') +
					' | Garant�a vence: ' + this.warrantyVoid +
					(this['void'] ? ' <strong>(vencida)</strong>' : '') +
					(this.notes ? '<br /><em>&nbsp;&nbsp;M�s informaci�n: ' + this.notes + '</em>' : '') +
					'</div>');
			});

			$('#tch_suggest .tch_s_row').click(function(){
				xajax_tchFormAcceptSale($(this)._for()||'', $(this).attr('cust')||'');
			});
		},
		fillForm: function(data, auto){	/* 'auto' means a script called, not the user */
			if (typeof(data) === 'object') {
				var frm = this.frm;

				$.each(data, function(key, val){
					if (frm[key]) {
						(frm[key]._type() == 'radio')
							? frm[key].filter('[value="'+val+'"]')._checked(true)
							: frm[key].val(val);
					}
				});

				$('#tch_id_system').attr('disabled', !!data.onSale);

				// Show Save and Print buttons
				auto || this.showButtons();

				// Take a snapshot of current customer's data
				this.takeSnapshot(data);
			}
		},
		showButtons: function(show){	/* to hide, pass false as param */
			$('#tch_buttons, #tch_submit').toggle(show);
		},
		submit: function(){
			if (!$('#tch_buttons:visible').length) return;

			if( !this.checkSnapshot() ){
				var msg = 'ATENCI�N:\n\n' +
					'Algunos datos del cliente fueron cambiados sin mediar\n' +
					'confirmaci�n. El contacto puede ser editado libremente, pero\n' +
					'no as� los restantes datos del cliente.\n\n' +
					'Si desea elegir un cliente diferente, realice la b�squeda por\n' +
					'cualquiera de los campos habilitados y seleccione un elemento\n' +
					' de la lista de sugerencias.\n\n' +
					'Pulse Aceptar para recargar los datos correspondientes a su\n' +
					'�ltima selecci�n, o Cancelar para elegir nuevamente un cliente\n' +
					'o factura.';
				return confirm(msg) ? this.restoreFromSnapshot() : null;
			};

			xajax_createTechVisit(xajax.getFormValues(this.frm.get(0)));
		},
		select: function(field){
			$('#tch_'+field).focus().select();
		},
		/* SNAPSHOT (security check to make sure the user is saving exactly what he sees */
		takeSnapshot: function(data){
			this.ss = {
				custNumber:	data['custNumber'],
				customer: data['customer'],
				address: data['address'],
				phone: data['phone']
			};
		},
		getSnapshot: function(){
			return this.ss;
		},
		checkSnapshot: function(){
			var ret = true;
			var frm = this.frm;
			$.each(this.ss, function(key, ss){
				ret = ret && (ss == frm[key].val());
			})
			return ret;
		},
		restoreFromSnapshot: function(){
			var frm = this.frm;
			$.each(this.ss, function(key, ss){
				frm[key] && frm[key].val(ss);
			});
		}
	};

	TechnicalForm.ini(data||[]);

	/* Enable save and print buttons */
	$('[name="frm_newTechVisit"]').submit(function(){
		return TechnicalForm.submit() & false;
	});
	$('#tch_save').click(function(){
		return TechnicalForm.submit() & false;
	});
	$('#tch_print').click(function(){
        /* TODO */
	});

};
function ini_editTechVisits( data ){ ini_createTechVisits( data ); };

function ini_techVisitsInfo( id ){
	var src = 'app/export/pdf/techVisit.php?id=' + id;

	$('#techVisitsPDF')._src(src + '#toolbar=0&navpanes=0&scrollbar=0');
	$('#techVisitsPrintPDF')._src(src + '&printer#toolbar=0');

	$('#btn_techVisitsEdit').click(function(e){
		getPage(e, 'editTechVisits', [id]);
	});

	$('#btn_techVisitsPrint').click(function(){
		window.frames.fra_techVisitsPrintPDF.print();
	});

    // AdminTechNotes
    $('#saveAdminTechNotes').click(function(){
		xajax_saveAdminTechNotes(id, $('#adminTechNotes textarea').val()||'');
	});
};