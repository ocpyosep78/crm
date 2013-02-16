function ini_config(){
	
	/* Object to handle item selection */
	window.List = {
		ID: null,
		IDs: [],
		filter: '%',
		sel: function( id ){
			if( !this.IDs[id] ) return;
			if( this.IDs[this.ID] ) this.IDs[this.ID].removeClass('selectedItem');
			xajax_updatePermitsBy(TAB, id, this.filter);
			this.IDs[this.ID=id].addClass('selectedItem');
			$('insertNameHere').innerHTML = this.IDs[id].getElement('A').innerHTML;
		},
		edt: function( id ){
		},
		del: function( id ){
		},
		attachListEventHandlers: function(){
			var that = this;
			/* Filters handlers */
			$$('.permitsFilter').forEach(function(tools){
				tools.getElements('SPAN').forEach(function(ftr){
					ftr.addEvent('click', function(){
						xajax_updatePermitsBy(TAB, List.ID, that.filter=ftr.getAttribute('FOR'));
					});
				});
			});
			/* Double click on a permission row */
			$$('.permitsList').forEach(function(list){
				var stat = list.getElement('.permitStat').value;
				list.getElements('.permitRow').forEach(function(row){
					row.addEvent('dblclick', function(e){
						e.stop();
						xajax_movePermit(TAB, stat, List.ID, row.getAttribute('FOR'));
					});
				});
			});
		}
	};
	
	/* Attach an event handler to each tab */
	$$('.tab').forEach(function(tab){
		tab.addEvent('click', function(e){
			getPage(e, 'config', [this.getAttribute('FOR')]);
		});
	});
	
	/* Attach event handlers to elements of the list (profiles, users) */
	$$('.listRow').forEach(function(pf){
		var ID = pf.getAttribute('FOR');
		List.IDs[ID] = pf;	/* Allows me to search by key, and link to the DOM element */
		pf.getElement('A').addEvent('click', function(e){
			List.sel( ID );
			e.stop();
		});
		pf.getElement('.editItem').addEvent('click', function(e){
			List.sel( ID );
			List.edt( ID );
			e.stop();
		});
		pf.getElement('.delItem').addEvent('click', function(e){
			List.sel( ID );
			List.del( ID );
			e.stop();
		});
	});
	
	switch( TAB ){
		case 'Profiles':
			$('btn_newProfile').addEvent('click', function(){
				xajax_addProfile( $('newProfile').value );
			});
			$('newProfile').addEvent('enter', function(){ $('btn_newProfile').click(); });
			List.sel( 2 );
		break;
	};
	
};