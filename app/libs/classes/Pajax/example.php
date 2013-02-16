<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/***************
** This example shows the basics of the class. Wrap response procedures in functions so they don't
**	affect a first run. Pajax::processRequests() will not do anything if there is no request to
**	process, so the flow will move past it.
** When the user presses the button in the form, Pajax sends a request. This time, the call to
**	Pajax::processRequests() will trigger the actual response, and terminate the program right after.
**	Just process the input and send regular javascript commands to Pajax::addResponse() and you're
**	done. The response printed in a temporary hidden frame will be read, parsed and evaluated by
**	Pajax javascript counterpart, with no further code needed.
***************/

	error_reporting(E_ALL);

	require_once('Pajax.class.php');
	$Pajax = new Pajax();
		
	# Test whether your Pajax is working as it should (response to example.html)
	function testResponse( $atts ){
		
		$GLOBALS['Pajax']->testInput( $atts );
		
	}
	
	$Pajax->processRequests();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

	<title>Pajax example</title>
	
	<?php $Pajax->printJavascript();
</head>
<body>

	<form name='example' enctype="multipart/form-data" onsubmit="Pajax.submitForm(this, 'testResponse', 60);" method="POST">
		<input type="hidden" name="MAX_FILE_SIZE" value="33554432" />	<!-- 32M -->
		<input type='file' name='fileX' />
		<br />
		<input type='text' name='field_1' value="Hello" />
		<input type='text' name='field_2' value="World!" />
		<br />
		<input type='submit' class='button' value='Test' onclick="$('test').innerHTML = '';" />
	</form>
	
	<br />
	
	<div id='test'></div>
	
</body>
</html>