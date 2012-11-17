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
			$('#insertNameHere').html(this.IDs[id].find('a').html());
		},
		edt: function(id) {},
		del: function(id){},
		attachListEventHandlers: function(){
			var that = this;
			/* Filters handlers */
			$('.permitsFilter span').click(function(){
				that.filter = $(this)._for();
				xajax_updatePermitsBy(TAB, List.ID, that.filter);
			});
			/* Double click on a permission row */
			$('.permitsList').each(function(i, list){
				var stat = $(list).find('.permitStat').val();
				$(list).find('.permitRow').dblclick(function(){
					xajax_movePermit(TAB, stat, List.ID, $(this)._for());
				});
			});
		}
	};

	/* Attach an event handler to each tab */
	$('.tab').click(function(e){
		getPage(e, 'config', [$(this)._for()]);
	});

	/* Attach event handlers to elements of the list (profiles, users) */
	$('.listRow').each(function(){
		var ID = this._for();
		List.IDs[ID] = this;	/* Allows me to search by key, and link to the DOM element */

		this.find('a').click(function(){
			return List.sel(ID) & false;
		});

		this.find('.editItem').click(function(){
			return List.sel(ID) & List.edt(ID) & false;
		});

		this.find('.delItem').click(function(){
			return List.sel(ID) & List.del(ID) & false;
		});
	}, true);

	switch (TAB) {
		case 'Profiles':
			$('#btn_newProfile').click(function(){
				xajax_addProfile($('#newProfile').val());
			});
			$('#newProfile').enter(function(){
				$('#btn_newProfile').click();
			});
			List.sel(2);
		break;
	};
}