{literal}
<style type="text/css">

/* <div id='alertsBox'><div id='alertsList'></div></div> */

	#alertsBox{
		display:none;
		position:fixed;
		height:18px;
		top:5px;
		left:400px;
		right:250px;
		margin:0px 2px;
		overflow:hidden;
		z-index:550;		/* Over navBar */
		color:#ffffff;
		background:#104382;
		border-radius:6px;
		-moz-border-radius:6px;
		-webkit-border-radius:6px;
		box-shadow:#c0c0c0 3px 3px 8px;
		-moz-box-shadow:#c0c0c0 3px 3px 8px;
		-webkit-box-shadow:#c0c0c0 3px 3px 8px;
	}
	#alertsBox SPAN.linkLike{
		color:#ff8050;
	}
	#alertsBox.shown{
		top:4px;
		right:20px;
		height:auto;
		max-height:95%;
		overflow:auto;
		border:solid 2px #000000;
		box-shadow:#909095 3px 3px 5px;
		-moz-box-shadow:#909095 3px 3px 5px;
		-webkit-box-shadow:#909095 3px 3px 5px;
	}
	#alertsList{
		margin-top:-1px;
		padding:0px;
		overflow:hidden;
	}
	#alertsList > DIV{
		padding:2px 5px 5px 5px;
		border-bottom:1px solid #c0c0c9;
		font-size:12px;
		font-family:Verdana, Arial, Helvetica, sans-serif;
		white-space:nowrap;
	}
	#alertsBox.shown #alertsList > DIV{
		padding:1px 3px;
		white-space:normal;
	}
	#alertsList IMG{
		height:12px;
		width:12px;
		margin-right:5px;
		vertical-align:middle;
		cursor:pointer;
	}
	.highlightedAlert{
		font-weight:bold;
		background:#e0e0e5;
	}
	
</style>

<script type="text/javascript">

	var sync = {
		firstLoad: true,
		list: {},
		lastRead: 0,
		alertRead: "<img src='app/images/buttons/delete.png' alt='quitar' title='quitar de la lista' />",
		request: function(){
			if( loggedIn ) silentXajax('sync', [loggedIn, {from: sync.lastRead}]);
		},
		requestRemoval: function(){
			if( this.parentNode.ref ) xajax_removeAlert( this.parentNode.ref );
			sync.remove( this.parentNode.ref );
		},
		process: function( alerts ){
			// Add new items to the (graphical) list
			for( x in alerts ) if( alerts.hasOwnProperty(x) && !(x in this.list) ) this.add(alerts[x]);
			if( !this.hasAlerts() ) this.hide();
			this.firstLoad = false;
		},
		add: function( al ){
			var that = this;
			this.list[al.id] = al;
			this.lastRead = Math.max(al.id||0, this.lastRead);
			var el = $(document.createElement('DIV'));
			el.innerHTML = this.alertRead + al.date + al.msg;
			el.ref = al.id;		/* Reference to alert's ID */
			el.getElement('IMG').addEvent('click', function(e){
				e.stop();
				that.requestRemoval.apply( this );
				clearTimeout( $('alertsBox').showAlertsTO );
			});
			$('alertsList').insertBefore(el, $('alertsList').firstChild);
			$('alertsList').parentNode.scrollTop = 0;
			this.show();
			if( !this.firstLoad ) this.highlightRow( el );
		},
		remove: function( x ){
			$('alertsList').getElements('DIV').forEach(function(el){
				if( el.ref == x ) el.parentNode.removeChild( el );
			});
			if( !$('alertsList').getElements('DIV').length ) this.hide();
		},
		show: function(){
			$('alertsBox').setStyle('display', 'block');
		},
		hide: function(){
			$('alertsBox').setStyle('display', 'none');
		},
		hasAlerts: function(){
			for( x in this.list ) if( this.list.hasOwnProperty(x) ) return true;
			return false;
		},
		highlightRow: function( row ){
			row.className = 'highlightedAlert';
			setTimeout(function(){ row.className = ''; }, 30000);
		}
	};
	
	window.addEvent('domready', function(){			/* Alerts & Sync */
		sync.request();
		var syncItvl = setInterval(sync.request, 5000);
/* xajax_removeAllAlerts(); unused for now, but functional */
		var $box = $('alertsBox');
		$box.addEvent('mouseenter', function(){
			$box.showAlertsTO = setTimeout(function(){
				$box.addClass('shown');
			}, 1000);
		});
		$box.addEvent('mouseleave', function(e){
			clearTimeout( $box.showAlertsTO );
			$box.removeClass('shown').scrollTop = 0;
		});
		$box.addEvent('click', function(){
			$box.toggleClass('shown');
			if( $box.hasClass('shown') ) $box.focus();
			else $box.scrollTop = 0;
		});
	});
	
</script>
{/literal}



<div id='alertsBox'><div id='alertsList'></div></div>