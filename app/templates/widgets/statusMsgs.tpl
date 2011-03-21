{literal}
<style type="text/css">

	#statusMsgs{
		position:fixed;
		top:30px;
		left:0px;
		right:0px;
		height:55px;
		padding:0px;
		margin:0px;
		background:-webkit-gradient(linear, left top, left bottom, from(#e0f0ff), to(#c09898));
		background:-moz-linear-gradient(top, #e0f0ff #c09898);
		filter:	progid:DXImageTransform.Microsoft.gradient(startColorstr='#e0f0ff', endColorstr='#c09898')
				alpha(opacity=0);
		color:#400000;
		font-weight:bold;
		text-align:center;
		font-family:Verdana, Geneva, sans-serif;
		font-size:13px;
		opacity:0;
		visibility:hidden;
		overflow:hidden;
		z-index:550;
	}
	#statusMsgs > DIV{
		display:inline-block;
		zoom:1;				/* IE */
		*display: inline;	/* IE */
		position:relative;
		top:50%;
		line-height:1.4em;
	}
	#statusMsgs.successStatus{
		background:-webkit-gradient(linear, left top, left bottom, from(#e0f0ff), to(#78a078));
		background:-moz-linear-gradient(top, #e0f0ff #78a078);
		filter:	progid:DXImageTransform.Microsoft.gradient(startColorstr='#e0f0ff', endColorstr='#78a078')
				alpha(opacity=0);
		color:#004000;
	}
	#statusMsgs.warningStatus{
		background:-webkit-gradient(linear, left top, left bottom, from(#e0f0ff), to(#b0b078));
		background:-moz-linear-gradient(top, #e0f0ff #b0b078);
		filter:	progid:DXImageTransform.Microsoft.gradient(startColorstr='#e0f0ff', endColorstr='#b0b078')
				alpha(opacity=0);
		color:#404000;
	}
	#statusMsgs > IMG{
		display:none;
		float:right;
		height:45px;
		width:45px;
		margin:6px 8px;
	}
	#statusMsgs.errorStatus > IMG.errorStatus,
	#statusMsgs.successStatus > IMG.successStatus,
	#statusMsgs.warningStatus > IMG.warningStatus{
		display:block;
	}
	
</style>

<script type='text/javascript'>
	
	function showStatus(txt, type){ msgBox.hide(-1).setType(type).show(txt); };
	function hideStatus( delay ){ return; msgBox.hide( delay ); };
	
	var msgBox = {
		hideTO: null,	/* timeOut */
		setType: function( type ){
			if( !isNaN( parseInt(type) )  ) type = parseInt(type) ? 'success' : 'error';
			$('statusMsgs').set('class',  type + 'Status');
			return this;
		},
		show: function(txt, to){		/* to := 0 for persistant msg */
			clearTimeout( this.hideTO );
			if( !$('statusMsgs') || !txt ) return this;
			var that = this;
			var height = txt.split(/<br ?\/>./, 3).length * 1.4;
			var Box = $('statusMsgs').set('opacity', 0.3);
			Box.getElement('DIV').set('html', txt).setStyle('marginTop', -(height/2) + 'em');
			new Fx.Tween(Box, {duration: 1500, fps:50}).start('opacity', 0.3, 1);
			if( to !== 0 ) this.hideTO = setTimeout(function(){ that.hide(); }, 10000);
			return this;
		},
		hide: function( delay ){
			if( $('statusMsgs') ){
				new Fx.Tween('statusMsgs', {duration: delay||600}).start('opacity', 0);
			};
			return this;
		}
	};
	
</script>
{/literal}



<div id='statusMsgs'>
	<img class='errorStatus' src='app/images/statusMsg/error.png'>
	<img class='successStatus' src='app/images/statusMsg/success.png'>
	<img class='warningStatus' src='app/images/statusMsg/warning.png'>
	<div></div>
</div>