<?php

/**
 ************************************
 * G E T T I N G   S T A R T E D
 ************************************
 *
 * Tool to process uploads without reloading the page.
 *
 *		FileForm::processRequests()
 *		FileForm::addResponse()
 *
 * Forms need to have an input of type FILE, with an attribute named 'ffcb' that
 * holds the name of the function that will attend the request (when the form is
 * submitted).
 *
 * FileForm::processRequests() needs to be added to the PHP script receiving the
 * form, after the function 'ffcb' has been defined. The form will be submitted
 * to the same page where the form is.
 *
 * FileForm::processRequests() will pass both $_POST and $_FILES, merged in one
 * array, as single parameter to the 'ffcb' callback function. You can then go
 * through this input, do what you need to do, and answer with valid JS commands
 * using FileForm::addResponse(). Once the callback ends, those commands will be
 * sent to the client and executed.
 *
 * In case you missed something, run ./example.php() for a hello-world example.
 */

class FileForm
{

	private static $cmd = '';


	public static function addResponse($cmd='')
	{
		$cmd = preg_replace('/[\n\r]/', '', trim($cmd, '; '));
		self::$cmd .= "parent.{$cmd};";
	}

	public static function processRequests()
	{
		if (isset($_POST['ffcb']))
		{
			$cb = $_POST['ffcb'];
			unset($_POST['ffcb'], $_POST['MAX_FILE_SIZE']);

			(empty($cb) || !function_exists($cb))
				? self::addResponse("alert('FileForm: Handler does not exist')")
				: call_user_func($cb, array_merge($_POST, $_FILES));

			if (!headers_sent())
			{
				header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: no-store, no-cache, must-revalidate");
				header("Cache-Control: post-check=0, pre-check=0", false);
				header("Pragma: no-cache");
			}

			die('<script type="text/javascript">' . self::$cmd . '</script>');
		}
	}

}