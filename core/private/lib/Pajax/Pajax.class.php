<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/***************
 *
 ****************************
	P S E U D O - A J A X   *
 ****************************
 *
 * pajax.class.php :: Pajax class
 *
 * @package pajax version 0.0.1
 * @copyright (c) 2010 by Diego Barreiro
 * @author diego.bindart@gmail.com
 * @license http://www.gnu.org/copyleft/lesser.html#SEC3 LGPL License
 *
 * Pajax is an open source PHP library for uploading or downloading files
 * without reloading the page, fast and easy, and getting a dynamic response
 * upon completing. Allows multiple uploads simultaneously.
 *
 * pajax is released under the terms of the LGPL license
 * http://www.gnu.org/copyleft/lesser.html#SEC3
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
**
**
***********************************
** A C K N O W L E D G E M E N T S
***********************************
**
** As you might guess from the names of Pajax's main methods (or by the name
**	of the class itself :-)), Pajax has been inspired by Xajax library. It
**	issues situations where ajax is not a solution (for security reasons most
**	of the times). It doesn't pretend to be an extension to Xajax; however, it
**	might very well fit this role. The basic idea is to make the use of Pajax
**	as simple and straightforward as possible, and I hope it will be, at least,
**	for Xajax users. ;)
**
** Xajax is a complete ajax library property of Jared White & J. Max Wilson,
**	released under the terms of the LGPL license:
**		http://www.gnu.org/copyleft/lesser.html#SEC3
** Its website is:
**		http://www.xajaxproject.org
**
**
***********************************
** G E T T I N G   S T A R T E D
***********************************
**
** Tool to process uploads, downloads and other special requests without
**	reloading the page. For a quick start, you only need to understand two PHP
**	functions: Pajax::processRequests() and Pajax::addResponse(), plus one JS
**	function: Pajax.submitForm().
** 
** Client-side function Pajax.submitForm needs 2 parameters, which are the form
**	itself (or its id) and the name of the PHP function that will process the form.
**	A third parameter, optional, sets the maximum time to wait for a response
**	(defaults to 30 seconds).
** Pajax::processRequests() needs to be added to the PHP script receiving the
**	form, in a place where you are sure the function called was already defined. By
**	default, it's the same page that generated the request. Keep in mind that calling
**	this function without an actual active request will be ignored, so it is safe to
**	add a call that could be triggered in regular execution turns.
**
** Recommended (optional):
**	- mootools or any other library that defines $() as an alias of getElementById()
**	- showStatus(), a user-defined function to output messages for the user.
**		[It could be just an alias of alert()]
**	- Supports gettext() emulator for JS (though it does not include it).
**	
** Preliminars:
**	- You just need a form that will trigger Pajax.submitForm() upon submision.
**		The easiest way is to add onsubmit="form2Frame(this, 'phpFunc');" to the form.
**
** Processing the form:
**	- Provided you call processRequests() in the right place, and the function exists
**		(and was defined at this point in execution), it will be called with 2 params:
**			1. All regular fields of the form (namely, $_POST array).
**			2. Uploaded files array (namely, $_FILES array).
**	- You can of course catch those 2 on your own, but what's the point? :-)
**	- To add responses, call Pajax::addResponse(), and pass js sentences as arguments.
**	- You can add a single command or an array of commands.
**	- Trailing semi-colon (;) is optional.
**
** And that's it? Pajax javascript object will wait for the response, execute the code
**	returned, display an error if a syntactically incorrect response appears, or tell
**	the user the request timed out if it does.
**
** In case you missed something, check example.php() for a hello-world example :-)
**
***************/

/*
	Pending:
		- define and document the protocol for responses
		- add code for checkFiles, forceUploads, includeStats, allowEmptyResp
		- find and hunt down potential problems with encoding
		- provide, test and document other uses apart from adding onsubmit to the form
		- correctly handle uploads that surpass PHP's directive 'post_max_size'
*/

	class Pajax{
		
/***************
** C O N F I G		This section might be editted (provided you know what you're doing :-) )
***************/
		
		private $response = array();
		
		# Whether to check uploaded files for errors.
		private $checkFiles = false;
		# Whether to take 'no file' (UPLOAD_ERR_NO_FILE) as an error.
		private $forceUploads = false;
		# Whether to include statistics in the response (such as time elapsed).
		private $includeStats = true;
		# Whether to accept no response as a valid response (not recommended).
		private $allowEmptyResp = false;
		
		
/***************
** C O N F I G		Methods available for personalizing the tool. It is on top just
**					as a reference for use. It should not be editted.
***************/

		# Whether to check uploaded files for errors.
		public function checkFilesOn( $bool=true ){
			$this->checkFiles = $bool;
		}
		
		# Whether to take 'no file' (UPLOAD_ERR_NO_FILE) as an error.
		public function forceUploadsOn( $bool=true ){
			$this->forceUploads = $bool;
		}
		
		# Whether to include statistics in the response (such as time elapsed).
		public function includeStatsOn( $bool=true ){
			$this->includeStats = $bool;
		}
		
		# Whether to accept no response as a valid response (not recommended).
		# This parameter is set when opening the page, NOT when processing a request.
		public function allowEmptyRespOn( $bool=true ){
			$this->allowEmptyResp = $bool;
		}
		
		
/***************
** M A I N			Main methods. It is advisable to know them all and to touch none. \o)
***************/
		
		/***************
		** Triggers the response only if there is an actual Pajax request to process.
		** You can call it safely at all times, and it'll act just when needed.
		** For example, you could initialize all constants, classes and load all function
		** packs, then add a call to Pajax::processRequests(). That way you make sure that
		** the function handling the request will have all common tools at hand.
		** <i>Usage:</i> <kbd>$Pajax->processRequests();</kbd>
		***************/
		public function processRequests(){
			
			# No special request to attend
			if( !isset($_POST['pajax']) ) return;
			
			$func = $_POST['pajax'];
			if( empty($func) || !function_exists($func) ){
				$this->addResponse("showStatus('Pajax error: handler does not exist')");
				die();
			}
			
			unset( $_POST['pajax'] );	# No need to forward internal elements
			unset( $_POST['MAX_FILE_SIZE'] );
			
			if( $this->checkFilesOn && ($chk=$this->checkFile($_FILES)) !== true ){
				$this->reportError( $chk );
			}
			
			call_user_func_array($func, array(array_merge($_POST, $_FILES)));
			
			$this->printResponse();
			
			exit();
			
		}
		
		/***************
		** @public method.
		** @params string|array (of strings): input must be well-formatted regular JS
		** code. Final semicolons ';' are optional.
		** Adds javascript code to be executed after finishing the process (whether it
		** is downloading, uploading or any other). Keep in mind that, during uploads,
		** PHP is frozen and cannot send any output whatsoever.
		** <i>Usage:</i> <kbd>$Pajax->addResponse('someJavascriptSentence');</kbd>
		***************/
		public function addResponse( $cmd='' ){
			
			if( is_array($cmd) ) foreach( $cmd as $singleCmd ) addResponse($singleCmd);
			elseif( $cmd ) $this->response[] = preg_replace('/;$/', '', $cmd).';';
			
		}
		
		/***************
		** @public method.
		** Add a call to this method in your pages wherever you want Pajax to work. It
		** prints client-side code to handle pseudo-ajax uploads and Pajax responses.
		***************/
		public function printJavascript(){
			
			$js = dirname(__FILE__)."/pajax.js";
			if(($fp=fopen($js, 'r')) && ($data=fread($fp, filesize($js))) ){
				echo "<script type='text/javascript'>{$data}</script>";
				fclose( $fp );
			}
			
		}
		
		# Debugging: Test whether your Pajax is receiving what it should
		public function testInput( $atts ){
		
			$res = 'This message has been generated using Pajax::addResponse()\\n';
			foreach( $atts as $key => $val ){
				$res .= "\\n{$key} -- {$val}";
				if( is_array($val) ){
					$res .= '(';
					foreach($val as $k => $v) $res .= "\\n    {$k} -- {$v}";
					$res .= '\\n)';
				}
			}
			$this->addResponse("alert('{$res}')");
			
		}
		
		
/***************
** P R I V A T E	Private methods, auxiliar functions.
***************/
		
		/***************
		** Check whether uploaded files arrived correctly (if there is any). If you want
		** Pajax to also check whether a file was selected at all, you need to call first
		** Pajax::forceFilePresent().
		***************/
		private function checkFile( $filesArray ){
			
/*

UPLOAD_ERR_OK			Value: 0; There is no error, the file uploaded with success.
UPLOAD_ERR_INI_SIZE		Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
UPLOAD_ERR_FORM_SIZE	Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
UPLOAD_ERR_PARTIAL		Value: 3; The uploaded file was only partially uploaded.
UPLOAD_ERR_NO_FILE		Value: 4; No file was uploaded.
UPLOAD_ERR_NO_TMP_DIR	Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.
UPLOAD_ERR_CANT_WRITE	Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.
UPLOAD_ERR_EXTENSION	Value: 8; A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.

*/		
			return $filesArray;
			
		}
		
		/***************
		** Outputs responses in a format client-side Pajax can understand. This output
		** will be hidden from the user.
		***************/
		private function printResponse(){
		
			header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			
			ob_start();
			
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta name="robots" content="none"></head><body><p>';
			foreach( $this->response as $cmd ) echo "<b>{$cmd}</b>";
			echo '</p></body></html>';
			
			ob_end_flush();
			
		}
		
	}

?>