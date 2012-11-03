function ini_config(){
	/* Object to handle item selection */
	window.List = {
		ID: null,
		IDs: [],
		filter: '%',
		sel: function( id ){
			if (!this.IDs[id]) return;
			this.IDs[this.ID] && this.IDs[this.ID].removeClass('selectedItem');
			xajax_updatePermitsBy(TAB, id, this.filter);
			this.IDs[this.ID=id].addClass('selectedItem');
			J('#insertNameHere').html(this.IDs[id].find('a').html());
		},
		edt: function( id ){
		},
		del: function( id ){
		},
		attachListEventHandlers: function(){
			var that = this;
			/* Filters handlers */
			J('.permitsFilter span').click(function(){
				that.filter = J(this).attr('for');
				xajax_updatePermitsBy(TAB, List.ID, that.filter);
			});
			/* Double click on a permission row */
			J('.permitsList').each(function(i, list){
				var stat = J(list).find('.permitStat').val();
				J(list).find('.permitRow').dblclick(function(){
					xajax_movePermit(TAB, stat, List.ID, J(this).attr('for'));
				});
			});
		}
	};
	
	/* Attach an event handler to each tab */
	J('.tab').click(function(e){
		getPage(e, 'config', [J(this).attr('for')]);
	});
	
	/* Attach event handlers to elements of the list (profiles, users) */
	J('.listRow').each(function(i, pf){
		var ID = J(pf).attr('for');
		List.IDs[ID] = J(pf);	/* Allows me to search by key, and link to the DOM element */
		J(pf).find('a').click(function(){
			return List.sel(ID) & false;
		});
		J(pf).find('.editItem').click(function(){
			return List.sel(ID) & List.edt(ID) & false;
		});
		J(pf).find('.delItem').click(function(){
			return List.sel(ID) & List.del(ID) & false;
		});
	});
	
	switch (TAB) {
		case 'Profiles':
			J('#btn_newProfile').click(function(){
				xajax_addProfile(J('#newProfile').val());
			});
			J('newProfile').enter(function(){
				J('#btn_newProfile').click();
			});
			List.sel(2);
		break;
	};
};