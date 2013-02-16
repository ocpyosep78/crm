function ini_createTechVisits( data ){
	/* Show form as soon as picture is fully loaded */
	function showForm(){
		clearTimeout( to );
		$('technicalForm').setStyle('display', 'block');
		$('tch_custNumber').focus();
	};
	var to = setTimeout(showForm, 2000);		/* Just in case pic loaded already (should not, but...) */
	$('technicalFormBg').addEvent('load', showForm);
	
	/* Collection of methods, attached to a DOM element (to be called through xajax) */
	var TechnicalForm = $('technicalForm').handler = {
		$form: {},
		ss: {},
		ini: function( data ){
			var that = this;
			/* Store a reference to the form and add names to its elements*/
			for( var i=0, el, els=document['frm_newTechVisit'].elements ; el=els[i] ; i++ ){
				var key = el.name = el.name||el.id.substr(4);
				if( typeof(this.$form[key]) == 'undefined' ) this.$form[key] = $(el);
				else if( this.$form[key].push ) this.$form[key].push( $(el) );
				else this.$form[key] = [this.$form[key], $(el)];
			};
			this.$form['ifIncomplete'].addEvent('keyup', function(){
				checkFormElement(that.$form, 'complete', 0);
			});
			this.$form['complete'].forEach(function(rad){
				rad.addEvent('mousedown', function(){
					if( this.checked ) setTimeout(function(){ rad.checked = false; }, 100);
				});
			});
			/* Attach event handlers to search boxes */
			$$('.tchSearch').forEach(function(btn){
				var by = btn.id.replace(/^tchSrch_/, '');
				btn.addEvent('click', function(e){ that.search.call(that, by); });
				that.$form[by].addEvent('enter', function(e){ that.search.call(that, by); });
				that.$form[by].addEvent('keydown', function(e){ if( e.key == 'enter' ) e.stop(); });
			});
            if(data.id_customer && !data.id_sale){
                xajax_tchFormAcceptSale('', data.id_customer);
            }else if( data ){
                this.fillForm(data, !data.id_sale);
            }
		},
		/* data is sent as parameter if provided, otherwise the whole form is sent */
		search: function( by ){
			xajax_tchFormSuggest(by, this.$form[by].value);
		},
		clearSuggest: function(){
			$('tch_suggest').innerHTML = '';
		},
		/* data is a JSON 2-dimensional object: first level is customers,
			second level is invoices (plus detail) */
		suggest: function( data ){
			this.clearSuggest();
			if( typeof(data) != 'object' ) return;
			for( var x in data ) if( data.hasOwnProperty(x) ) this.addCustomersSuggest( data[x] );
			if( !$('tch_suggest').innerHTML ){
				var msg = 'No hay resultados que coincidan con su búsqueda';
				$('tch_suggest').innerHTML = "<div class='tch_s_empty'>" + msg + "</div>";
			};
		},
		addCustomersSuggest: function( data ){	/* Builds the list of suggested invoices/sales/installs */
			var customer = data['customer'];
			var rows = data['rows'];
			$('tch_suggest').innerHTML += "<div class='tch_s_customer'>Cliente " + data.customer + "</div>";
			$('tch_suggest').innerHTML += "<div class='tch_s_contact'>" +
				(data.contact ? 'Contacto: ' + data.contact + '<br />' : '') + "</div>";
			$('tch_suggest').innerHTML += "<div class='tch_s_row tch_s_noInvoice' cust='" + data.id_customer + "'>" +
				"Servicio Técnico sin factura previa</div>";
			for( var i=0, row ; row=data['rows'][i] ; i++ ){
				$('tch_suggest').innerHTML += "<div class='tch_s_row' for='" + row.onSale + "'>" +
					"Factura: " + row.invoice + 
					(row.system ? ' (' + row.system + ")" : '') + 
					' | Garantía vence: ' + row.warrantyVoid +
					(row['void'] ? ' <strong>(vencida)</strong>' : '') +
					(row.notes ? '<br /><em>&nbsp;&nbsp;Más información: ' + row.notes + '</em>' : '') +
					'</div>';
			};
			$('tch_suggest').getElements('.tch_s_row').forEach(function(row){
				row.addEvent('click', function(){
					xajax_tchFormAcceptSale(this.getAttribute('FOR')||'', this.getAttribute('CUST')||'');
				});
			});
		},
		fillForm: function(data, auto){	/* 'auto' means a script called, not the user */
			if( typeof(data) != 'object' ) return;
			var els = this.$form;
			var textTypes = {text:1, hidden:1, password:1};
			for( var x in data ){
				if(!els[x] || !data.hasOwnProperty(x) || !isNaN(x)) continue;
				if( textTypes[els[x].type] || els[x].nodeName == 'SELECT' ) els[x].value = data[x];
				else checkFormElement(els, x, data[x]);		/* Radio buttons collection */
			};
			$('tch_id_system').disabled = !!data.onSale;
			/* Show Save and Print buttons and take a snapshot of current customer's data */
			if( !auto ) this.showButtons();
			this.takeSnapshot( data );
		},
		showButtons: function( show ){	/* to hide, pass false as param */
			$('tch_buttons').setStyle('display', (show === false) ? 'none' : 'block');
			$('tch_submit').setStyle('display', (show === false) ? 'none' : 'block');
		},
		submit: function( e ){
			if( $('tch_buttons').style.display !== 'block' ) return;
			if( !this.checkSnapshot() ){
				var msg = 'ATENCIÓN:\n\n' +
					'Algunos datos del cliente fueron cambiados sin mediar\n' +
					'confirmación. El contacto puede ser editado libremente, pero\n' +
					'no así los restantes datos del cliente.\n\n' +
					'Si desea elegir un cliente diferente, realice la búsqueda por\n' +
					'cualquiera de los campos habilitados y seleccione un elemento\n' +
					' de la lista de sugerencias.\n\n' +
					'Pulse Aceptar para recargar los datos correspondientes a su\n' +
					'última selección, o Cancelar para elegir nuevamente un cliente\n' +
					'o factura.';
				return confirm(msg) ? this.restoreFromSnapshot() : null;
			};
			xajax_createTechVisit( xajax.getFormValues($(document.forms['frm_newTechVisit'])) );
		},
		select: function( field ){
			if( !(field=$('tch_'+field)) ) return;
			field.focus();
			field.select();
		},
		/* SNAPSHOT (security check to make sure the user is saving exactly what he sees */
		takeSnapshot: function( data ){
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
			for( var x in this.ss ){
				if( this.ss.hasOwnProperty(x) && this.ss[x] != this.$form[x].value ) return false;
			};
			return true;
		},
		restoreFromSnapshot: function(){
			for( var x in this.ss ) if( this.ss.hasOwnProperty(x) && this.$form[x] ){
				this.$form[x].value = this.ss[x];
			};
		}
	};
	
	TechnicalForm.ini( data||[] );
	
	/* Enable save and print buttons */
	$(document.forms.frm_newTechVisit).addEvent('submit', function(e){ TechnicalForm.submit(e); return false; });
	$('tch_save').addEvent('click', function(e){ TechnicalForm.submit(e); });
	$('tch_print').addEvent('click', function(){
        /* TODO */
	});
	
};
function ini_editTechVisits( data ){ ini_createTechVisits( data ); };

function ini_techVisitsInfo( id ){
	var src = 'app/export/pdf/techVisit.php?id=' + id;
	$('techVisitsPDF').src = src + '#toolbar=0&navpanes=0&scrollbar=0';
	$('techVisitsPrintPDF').src = src + '&printer#toolbar=0';
	$('btn_techVisitsEdit').addEvent('click', function(e){ getPage(e, 'editTechVisits', [id]); });
	$('btn_techVisitsPrint').addEvent('click', function(){
		window.frames.fra_techVisitsPrintPDF.print();
	});
    // AdminTechNotes
    if ($('saveAdminTechNotes')){
        $('saveAdminTechNotes').addEvent('click', function(){
            var note = $('adminTechNotes').getElement('textarea').value;
            xajax_saveAdminTechNotes(id, note||'');
        })
    }
};