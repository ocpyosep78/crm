<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<meta http-equiv="Last-Modified" content="0">
	<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
	<meta name="robots" content="none">

	{literal}
	<style type="text/css">
		BODY{ margin:50px; }
		H2{ margin-bottom:3px; }
		.subTitle{ font-family:Arial, Helvetica, sans-serif; font-size:12px; }
		UL{ margin:60px 0px; }
		LI{ margin:30px 0px; color:#802020; }
		LI SPAN{ font-weight:bold; }
	</style>
	{/literal}

</head>
<body>

	<h2>Advertencias</h2>
	<div class='subTitle'>
		Lea atentamente las advertencias que siguen, antes de continuar con la generaciÃÂ³n del archivo PDF
	</div>

	<ul>{foreach from=$warnings item=warning}<li>{$warning}</li>{/foreach}</ul>

	<form method='GET'>
		{foreach from=$_GET key=k item=v}<input type='hidden' name='{$k}' value='{$v}' />{/foreach}
		<input type='hidden' name='validated' />
		<input type='submit' value='Intentar generar el PDF ignorando las advertencias' />
	</form>

</body>
</html>