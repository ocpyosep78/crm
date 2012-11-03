function ini_home(){
	J('#editAccInfo').click(function(e){
		getPage(e, 'editAccInfo');
	});
};

function ini_createEvent(id_event){		/* Agenda */
	J('#btn_saveEvent').click(function(){
		if(J('#evt_iniTime').val() === '' || !validateTimeInput('evt_iniTime', 'value')){
			return FTshowTip('evt_iniTime', 'Hora de inicio inválida.');
		};	/* Preformat time, validate and apply changes if valid */
		if(J('#evt_endTime').val() !== '' && !validateTimeInput('evt_endTime', 'value')){
			return FTshowTip('evt_endTime', 'Hora de finalización inválida.');
		};
		if(readTextArea('evt_event').trim() === ''){
			return FTshowTip('evt_event', 'Debe proporcionar una descripción del evento.');
		};
		xajax_createEvent(xajax.getFormValues('frmEditEvent'), id_event || 0);
	});

	J('#evt_target').change(function(){
		selectOption(J('#remind'), J(this).val(), 'value');
	});
};

function ini_editEvent(){		/* Agenda */
	ini_createEvent(J('#id_event').val());
};

function ini_agenda(){
	setAgendaHandlers();

	// Tie events to each day
	J('.agenda_dayWrapper').each(function(i, block){
		var preview = function(){
			showPage('agendaDay', [J(block).attr('for'), getFilters()]);
		};
		J(block).find('.agenda_dayDate').click(preview);
		J(block).find('.agenda_dayName').click(preview);
	});

	J('#agenda_move').find('img').each(function(i, im){
		J(im).attr('for') && J(im).click(function(e){
			getPage(e, 'agenda', [J(im).attr('for'), getFilters()]);
		});
	});

	J('#btn_createEvent').click(function(e){
		getPage(e, 'createEvent', []);
	});
	
	J("#agenda_calendar").datepicker({
		showOn: 'button',
		dateFormat: 'yy/mm/dd',
		autoSize: true,
		showAnim: 'slideDown',
		buttonImage: 'app/images/agendaTools/calendar.gif',
		buttonImageOnly: true,
		beforeShow: function(input, inst){
			var itvl = setInterval(function(){
				var selDay = J(inst.dpDiv).find('.ui-datepicker-current-day');
				if (selDay.length)
				{
					selDay.siblings('td').find('a').addClass('ui-state-active');
					clearInterval(itvl);
				}
			}, 20);
		},
		onSelect: function(day, inst){
			getPage('agenda', [day, getFilters()]);
		}
	});
	
	function getFilters(){
		var filters = {};
		J('.sel_agendaFilters').each(function(i, sel){
			filters[J(sel).attr('filter')] = J(sel).val();
		});
		return filters;
	};

	function filterAgenda(e){
		var resched = J('#showRescheduled').attr('checked') ? 1 : 0;
		getPage(e, 'agenda', [J('#thisDate').val(), getFilters(), resched]);
	};

	J('.sel_agendaFilters').each(function(i, sel){
		J(sel).change(filterAgenda);
	});

	J('.eventRescheduled')[J('#showRescheduled').attr('checked') ? 'show' : 'hide']();

	J('#showRescheduled').click(function(){
		J('.eventRescheduled').toggle();
	});

	// Animate Event Tools
	J('.eventUnit:not(.eventClosed)').hover(function(){
		J(this).find('.eventTools img').animate({
			'right': 0
		}, {'duration': 100, 'queue': false});
		J(this).children(':not(.eventTools)').animate({
			'opacity': 0.6
		});
	}, function(){
		J(this).find('.eventTools img').animate({
			'right': -200
		}, {'duration': 500, 'queue': false});
		J(this).children(':not(.eventTools)').animate({
			'opacity': 1
		});
	});
	
	// Activate Event Tools
	J('.eventTools img').click(function(e){
		var eventID = J(this).parents('.eventUnit:first').find('[name="id_event"]').val();

		switch (J(this).attr('for'))
		{
			case 'edit'  : getPage(e, 'editEvent', [eventID])
				break;
			case 'cancel': closeAgendaEvent(eventID, '', true);
				break;
			case 'close' : closeAgendaEvent(eventID);
				break;
			default:
				return;
		}
		return false;
	});
};

function ini_agendaDay(){
	J('.eventUnit').click(function(){
		xajax_eventInfo(J(this).find('input[type="hidden"]').val());
	});
	
	function getFilters(){
		var filters = {};
		J('.sel_agendaFilters').each(function(i, sel){
			filters[J(sel).attr('filter')] = J(sel).val();
		});
		return filters;
	};

	function filterAgenda(e){
		var resched = J('#showRescheduled').attr('checked') ? 1 : 0;
		getPage(e, 'agenda', [J('#thisDate').val(), getFilters(), resched]);
	};

	J('.sel_agendaFilters').each(function(i, sel){
		J(sel).change(filterAgenda);
	});

	J('.eventRescheduled')[J('#showRescheduled').attr('checked') ? 'show' : 'hide']();

	J('#showRescheduled').click(function(){
		J('.eventRescheduled').toggle();
	});
};

function ini_activity(){
	var msg = "¿Está seguro que desea descartar esta entrada?\n" + 
		"Si continúa, el elemento no volverá a aparecer en esta lista.";

	J('.closeActivityEntry').each(function(i, btn){
		J(btn).attr('for') && J(btn).click(function(){
			confirm(msg) && xajax_closeActivityEntry(J(btn).attr('for'));
		});
	});
};

function ini_activity_technical(){ ini_activity(); };

function ini_activity_sales(){ ini_activity(); };