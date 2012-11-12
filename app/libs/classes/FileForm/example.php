<?php

/**
 * FileForm requires jQuery library 1.7 or above.
 */

function testResponse($input)
{
	$res = '<pre>' . var_export($input, true) . '</pre>';
	$cmd = 'jQuery("#test").html("' . nl2br(addslashes($res)) . '")';

	FileForm::addResponse($cmd);
}

require_once('FileForm.php');
FileForm::processRequests();

?><!DOCTYPE html PUBLIC "//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>FileForm Example</title>

	<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js'></script>
	<script type='text/javascript' src='fileform.js'></script>
	<script type='text/javascript'>
	  jQuery(function($){
		$(':submit').click(function(){
			$('test').html('');
		});
	  });
	</script>
</head>
<body>

	<form name='example'>
		<input type="hidden" name="MAX_FILE_SIZE" value="33554432" />   <! 32M, optional tag >
		<input type='file' name='fileX' ffcb='testResponse' />          <! cb attribute is the target function >
		<br />
		<input type='text' name='field_1' value="Hello" />
		<input type='text' name='field_2' value="World!" />
		<br />
		<input type='submit' value='Test' />
	</form>

	<br />

	<div id='test'></div>

</body>
</html>