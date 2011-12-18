<?php

/**
 * DEPENDS ON:
 * 
 *    class Snippet_Layers (always)
 *        layer ajax (always for #addSnippet)
 *            #getResponse()
 *    class Snippet_Initialize (always)
 *        #getSnippet( string $snippet )
 *    constant PAGE_CONTENT_BOX (for #addSnippet when $params['writeTo'] is empty)
 */

	# Error messages when retrieving a snippet can go either as a status message,
	# however the application handles it (through communications layer), or as the
	# HTML of the snippet itself.
	# 'status': HTML on error is an empty string, and error message is shown as
	#           a status message
	# 'html'  : HTML on error is the formatted error message, no status msg shown
	# 
	# NOTE: On success, the only output type is status
	defined('SNIPPET_ERROR_OUTPUT')
		|| define('SNIPPET_ERROR_OUTPUT', 'status');
	
	# Engines supported: mysql
	defined('SNIPPETS_SQL_ENGINE')
		|| define('SNIPPETS_SQL_ENGINE', 'mysql');
	
	
	# Internal library structure
	define('SNIPPET_LIB_PATH', dirname(__FILE__).'/lib');
	define('SNIPPET_HANDLERS_PATH', dirname(__FILE__).'/handlers');
	
	# Paths for output (templates, images, styles, jScripts)
	define('SNIPPET_OUTPUT', SNIPPET_PATH.'/output');
	define('SNIPPET_TEMPLATES', SNIPPET_OUTPUT.'/templates');
	define('SNIPPET_IMAGES', SNIPPET_OUTPUT.'/images');
	define('SNIPPET_STYLES', SNIPPET_OUTPUT.'/styles');
	define('SNIPPET_SCRIPTS', SNIPPET_OUTPUT.'/scripts');
	
	
	# Path to snippet definition files
	defined('SNIPPET_DEFINITION_PATH')
		|| define('SNIPPET_DEFINITION_PATH', dirname(__FILE__).'/defs');
		

	# Include common internal classes
	require_once( SNIPPET_LIB_PATH.'/Initialize.lib.php' );
	require_once( SNIPPET_LIB_PATH.'/Layers.lib.php' );
	require_once( SNIPPET_LIB_PATH.'/Tools.lib.php' );
	require_once( SNIPPET_LIB_PATH.'/Validation.lib.php' );
	

	class Snippet{
	
		private $Layers;
	
		public function __construct(){
			
			$this->Layers = new Snippet_Layers;
		
		}
	
		/**
		 * string getSnippet ( string $snippet , string $code [, array $params] )
		 * @access: public
		 * 
		 * @overview: creates an Initialize object and calls its #getSnippet,
		 *            forwarding what it returns as its own return value
		 *            (HTML string expected)
		 * 
		 * @onError: this function doesn't handle errors
		 * 
		 * @returns: an HTML string
		 */
		public function getSnippet($snippet, $code, $params=array()){
		
			# Accept a string as params, taking it as $params['modifier']
			is_array($params) || $params = array('modifier' => $params);
			
			$Initialize = new Snippet_Initialize($code, $params);
			
			return $Initialize->getSnippet( $snippet );
			
		}
	
		/**
		 * object addSnippet ( string $snippet , string $code [, array $params] )
		 * @access: public
		 * 
		 * @overview: gets an HTML output from self#getSnippet, and writes
		 *            it to the page (through ajax layer).
		 * 
		 * @onError: this function does not handle errors
		 * 
		 * @returns: an ajax Response Object, provided by the ajax layer
		 */
		public function addSnippet($snippet, $code, $params=array()){
		
			# Accept a string as params, taking it as $params['modifier']
			is_array($params) || $params = array('modifier' => $params);
			
			# Make sure the param 'writeTo' is set (it flags the request
			# as addSnippet, which means it needs to be printed)
			isset($params['writeTo']) ||  $params['writeTo'] = PAGE_CONTENT_BOX;
			
			# We don't want the resulting HTML here. Initialize object will
			# print it and initialize it (through the ajax layer) when
			# it sees the writeTo parameter
			$this->getSnippet($snippet, $code, $params);
			
			# We do return, instead, the ajax response object, that holds
			# the actual responses built elsewhere
			return $this->Layers->get('ajax')->getResponse();
			
		}
		
	}