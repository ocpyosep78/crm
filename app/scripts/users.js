function ini_usersInfo() {
	$('#editAccInfo').click(function(e){ getPage(e, 'editAccInfo'); });
	$('#editUsers').click(function(e){ getPage(e, 'editUsers', [$(this)._for()]); });
}