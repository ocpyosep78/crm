{literal}
<style type="text/css">
	#loadingGif{
		display:none;
		position:fixed;
		top:50%;
		left:50%;
		height:26px;
		width:150px;
		margin:-30px 0px 0px -80px;
		padding:5px 0px;
		border-radius:4px;
		-moz-border-radius:4px;
		-webkit-border-radius:4px;
		background: -webkit-gradient(linear, left top, left bottom, from(#000040), to(#90a3c0));
		background: -moz-linear-gradient(top, #f0f0f0, #90a3c0);
		filter:	alpha(opacity=80)
				progid:DXImageTransform.Microsoft.gradient(startColorstr='#000040', endColorstr='#90a3c0');
		opacity:0.8;
		z-index:2000;
	}
	#loadingGif SPAN{
		margin-left:8px;
		color:#ffffe0;
		font-family:Times;
		font-size:12px;
		font-weight:bold;
	}
	#loadingGif IMG{
		margin-top:3px;
		height:8px;
		width:150px;
	}
</style>

<script type="text/javascript">
	function showLoading( h ){
		J('#loadingGif').toggle(h);
	};
	function hideLoading(){
		showLoading(false);
	};
	
	J(function(){
		if (window.xajax) {
			xajax.loadingFunction = showLoading;
			xajax.doneLoadingFunction = hideLoading;
			xajax.loadingFailedFunction = hideLoading;
		};
	});

	J(window).on('beforeunload', showLoading);
</script>
{/literal}

<div id='loadingGif'>
	<span>Espere por favor...</span><br />
	<img src="app/images/loading.gif" alt="Cargando..." />
</div>