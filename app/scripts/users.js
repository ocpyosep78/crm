function ini_usersInfo(){
	J('#editAccInfo').click(function(e){ getPage(e, 'editAccInfo'); });
	J('#editUsers').click(function(e){ getPage(e, 'editUsers', [this._for()]); });
	setAgendaHandlers();
}