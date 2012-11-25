function ini_home(){
	return ini_agenda();
}

function ini_createEvent(id_event){		/* Agenda */
	function validateTimeInput(el){
		var p = el.val().split(':'),
		    val = p[0].fill(2, 0, true) + ':' + (p[1]||'').fill(2, 0, true),
		    ret = val.test(/^(2[0-3]|[01]\d):[0-5]\d$/) ? val : '';
		return ret && el.val(ret);
	}

	$('#btn_saveEvent').click(function(){
		if ($('#evt_iniTime').val() === '' || !validateTimeInput($('#evt_iniTime'))) {
			return showTip('evt_iniTime', 'Hora de inicio inválida.');
		};	/* Preformat time, validate and apply changes if valid */
		if ($('#evt_endTime').val() !== '' && !validateTimeInput($('#evt_iniTime'))) {
			return showTip('evt_endTime', 'Hora de finalización inválida.');
		};
		if ($.trim($('#evt_event').val()) === '') {
			return showTip('evt_event', 'Debe proporcionar una descripción del evento.');
		};

		var data = xajax.getFormValues($('form[name="frmEditEvent"]').get(0));
		xajax_createEvent(data, id_event || 0);
	});

	$('#evt_target').change(function(){
		selectOption($('#remind'), $(this).val(), 'value');
	});
}

function ini_editEvent(){		/* Agenda */
	ini_createEvent($('#id_event').val());
}

function ini_agenda(){
	// Tie events to each day
	$('.agenda_dayWrapper').each(function(i, block){
		var preview = function(){
			showPage('agendaDay', [$(block)._for(), getFilters()]);
		};
		$(block).find('.agenda_dayDate').click(preview);
		$(block).find('.agenda_dayName').click(preview);
	});

	$('#agenda_move').find('img').each(function(i, im){
		$(im)._for() && $(im).click(function(e){
			getPage(e, 'agenda', [$(im)._for(), getFilters()]);
		});
	});

	$('#btn_createEvent').click(function(e){
		getPage(e, 'createEvent', []);
	});

	$("#agenda_calendar").datepicker({
		showOn: 'button',
		dateFormat: 'yy/mm/dd',
		autoSize: true,
		showAnim: 'slideDown',
		buttonImage: 'app/images/agendaTools/calendar.gif',
		buttonImageOnly: true,
		beforeShow: function(input, inst){
			var itvl = setInterval(function(){
				var selDay = $(inst.dpDiv).find('.ui-datepicker-current-day');
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
		$('.sel_agendaFilters').each(function(i, sel){
			filters[$(sel).attr('filter')] = $(sel).val();
		});
		return filters;
	};

	function filterAgenda(e){
		var resched = $('#showRescheduled').attr('checked') ? 1 : 0;
		getPage(e, 'agenda', [$('#thisDate').val(), getFilters(), resched]);
	};

	$('.sel_agendaFilters').each(function(i, sel){
		$(sel).change(filterAgenda);
	});

	$('.eventRescheduled').toggle($('#showRescheduled').attr('checked'));

	$('#showRescheduled').click(function(){
		$('.eventRescheduled').toggle();
	});

	// Animate Event Tools
	$('.eventUnit:not(.eventClosed)').hover(function(){
		$(this).find('.eventTools img').animate({
			'right': 0
		}, {'duration': 100, 'queue': false});
		$(this).children(':not(.eventTools)').animate({
			'opacity': 0.6
		});
	}, function(){
		$(this).find('.eventTools img').animate({
			'right': -200
		}, {'duration': 500, 'queue': false});
		$(this).children(':not(.eventTools)').animate({
			'opacity': 1
		});
	});

	// Activate Event Tools
	$('.eventTools img').click(function(e){
		var eventID = $(this).parents('.eventUnit:first').find('[name="id_event"]').val();

		switch ($(this)._for())
		{
			case 'edit'  : getPage(e, 'editEvent', [eventID])
				break;
			case 'cancel': closeAgendaEvent(eventID, '', true);
				break;
			case 'close' : closeAgendaEvent(eventID, '', false);
				break;
			default:
				return;
		}
		return false;
	});
}

function ini_agendaDay(){
	function getFilters(){
		var filters = {};
		$('.sel_agendaFilters').each(function(i, sel){
			filters[$(sel).attr('filter')] = $(sel).val();
		});
		return filters;
	};

	function filterAgenda(e){
		var resched = $('#showRescheduled').attr('checked') ? 1 : 0;
		getPage(e, 'agenda', [$('#thisDate').val(), getFilters(), resched]);
	};

	$('.sel_agendaFilters').each(function(i, sel){
		$(sel).change(filterAgenda);
	});

	$('.eventRescheduled').toggle($('#showRescheduled').attr('checked'));

	$('#showRescheduled').click(function(){
		$('.eventRescheduled').toggle();
	});
}

function ini_activity(){
	var msg = "¿Está seguro que desea descartar esta entrada?\n" +
		"Si continúa, el elemento no volverá a aparecer en esta lista.";

	$('.closeActivityEntry').each(function(i, btn){
		$(btn)._for() && $(btn).click(function(){
			confirm(msg) && xajax_closeActivityEntry($(btn)._for());
		});
	});
}

function ini_activity_technical(){ ini_activity(); }

function ini_activity_sales(){ ini_activity(); }