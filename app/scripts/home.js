function ini_home(){
	if( $('editAccInfo') ){
		$('editAccInfo').addEvent('click', function(e){ getPage(e, 'editAccInfo'); });
	};
};

function ini_createEvent( id_event ){		/* Agenda */
	$('btn_saveEvent').addEvent('click', function(){
		if( $('evt_iniTime').value === '' || !validateTimeInput('evt_iniTime', 'value') ){
			return FTshowTip('evt_iniTime', 'Hora de inicio inválida.');
		};	/* Preformat time, validate and apply changes if valid */
		if( $('evt_endTime').value !== '' && !validateTimeInput('evt_endTime', 'value') ){
			return FTshowTip('evt_endTime', 'Hora de finalización inválida.');
		};
		if( readTextArea('evt_event').trim() === '' ){
			return FTshowTip('evt_event', 'Debe proporcionar una descripción del evento.');
		};
		xajax_createEvent(xajax.getFormValues('frmEditEvent'), id_event || 0);
	});
	$('evt_target').addEvent('change', function(){
		selectOption($('remind'), this.value, 'value');
	});
};

function ini_editEvent(){		/* Agenda */
	ini_createEvent( $('id_event').value );
};

function ini_agenda(){
	setAgendaHandlers();
	// Tie events to each day
	$$('.agenda_dayWrapper').forEach(function(block){
		var preview = function(){ showPage('agendaDay', [block.getAttribute('FOR')]); };
		block.getElement('.agenda_dayDate').addEvent('click', preview);
		block.getElement('.agenda_dayName').addEvent('click', preview);
	});
	$('agenda_move').getElements('IMG').forEach(function(im){
		if( im.getAttribute('for') ) im.addEvent('click', function(e){
			getPage(e, 'agenda', [im.getAttribute('for'), getFilters()]);
		});
	});
	$('btn_createEvent').addEvent('click', function(e){
		getPage(e, 'createEvent', []);
	});
	$('epochTrigger').epochHandler = function(){
		getPage('agenda', [this.value, getFilters()]);
	};
	function getFilters(){
		var filters = {};
		$$('.sel_agendaFilters').forEach(function(sel){ filters[sel.getAttribute('filter')] = sel.value; });
		return filters;
	};
	function filterAgenda(e){
		getPage(e, 'agenda', [$('thisDate').value, getFilters(), $('showRescheduled').checked ? 1 : 0]);
	};
	$$('.sel_agendaFilters').forEach(function(sel){ sel.onchange = filterAgenda; });
	$('showRescheduled').onclick = function(){
		var that = this;
		$$('.eventRescheduled').forEach(function(p){
			$(p).setStyle('display', that.checked ? 'block' : 'none');
		});
	};
	$('showRescheduled').onclick();
};
function ini_agendaDay(){
	$$('.eventUnit').forEach(function(evt){
		evt.addEvent('click', function(){ xajax_eventInfo( $(this).getElement('INPUT').value ); });
	});

	function getFilters(){
		var filters = {};
		$$('.sel_agendaFilters').forEach(function(sel){ filters[sel.getAttribute('filter')] = sel.value; });
		return filters;
	};
	function filterAgenda(e){
		showPage(e, 'agendaDay', [$('thisDate').value, getFilters(), $('showRescheduled').checked ? 1 : 0]);
	};
	$$('.sel_agendaFilters').forEach(function(sel){ sel.onchange = filterAgenda; });
	$('showRescheduled').onclick = function(){
		var that = this;
		$$('.eventRescheduled').forEach(function(p){
			$(p).setStyle('display', that.checked ? 'block' : 'none');
		});
	};
	$('showRescheduled').onclick();
};

function ini_activity_technical(){ ini_activity(); };
function ini_activity_sales(){ ini_activity(); };
function ini_activity(){
	var msg = "¿Está seguro que desea descartar esta entrada?\n" + 
		"Si continúa, el elemento no volverá a aparecer en esta lista.";
	$$('.closeActivityEntry').forEach(function(btn){
		var id = btn.getAttribute('FOR');
		if( id ) btn.addEvent('click', function(){
			if( confirm(msg) ) xajax_closeActivityEntry( id );
		});
	});
};