function ini_usersInfo(){
	($('editAccInfo')||$E).addEvent('click', function(e){ getPage(e, 'editAccInfo'); });
	($('editUsers')||$E).addEvent('click', function(e){ getPage(e, 'editUsers', [this.getAttribute('FOR')]); });
	setAgendaHandlers();
};