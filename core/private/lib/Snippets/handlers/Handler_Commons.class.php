<?php

/**
 * DEPENDS ON:
 * 
 *    class Snippet_Layers
 *        layer ajax
 *            #display( string $msg , string $errType )
 *        layer error
 *            #Error( string $msg )
 *    class Snippet_Handler_Interpreter
 *        #inject( string $snippet , string $code , array $params )
 *        #aquire()
 *        #validate()
 *        #
 *        #
 *    constant SNIPPETS_DEFINITIONS_PATH
 */

/**
 * abstract class Snippets_Handlers_Commons
 * 
 * @overview: This class ties together a particular Handler class (descending
 *            from this one) with the information provided by a definition file
 *            (created by users, following conventions).
 * 
 *            It gets assistance from its sibling Snippets_Handler_Interpreter
 *            for parsing and interpretting the definitions, thus decompressing
 *            the logic it has to process for each snippet request. Commons
 *            stays at a higher level and receives digested data, not minding
 *            how or where it came from.
 * 
 *            Definition files extend Snippets_Handler_Interpreter, so we get
 *            all of its methods by loading the definition class script and
 *            instantiating it. Within the class, it is stored as @Source.
 * 
 *            This class attempts to bring results even if everything's broken,
 *            falling back to defaults or empty sets of data, to avoid fatal
 *            errors for bad configuration. It does, however, cause warnings
 *            in the shape of messages for the user, by calling the ajax layer
 *            method #display(string $msg, mixed $type).
 */


/*

	How it's used by Pages:

		$Handler->start($code, $params);
		$HTML = $Handler->getSnippet();
		if( $params['writeTo'] ){
			$this->Layers->get('ajax')->write($params['writeTo'], $HTML);
			$Handler->initializeSnippet();
		}
	
*/
 
  	require_once(dirname(__FILE__).'/Handler_Interpreter.class.php');
	

	abstract class Snippets_Handlers_Commons{
	
		protected $snippet;
		
		protected $code;
		protected $params;
		
		private $Source;
		
	
		public function __construct( $snippet ){
			
			$this->snippet = $snippet;
			
		}
		
		public function start($code, $params){
			
			$this->code = $code;
			$this->params = $params;
			
			# Attempt to load the definition file
			$file = SNIPPETS_DEFINITIONS_PATH."/{$code}.def.php";
			$class = "Snippet_def_{$code}";
			if( is_file($file) ) require_once( $file );
			
			# If not found, use the naked Interpreter as $Source
			if( class_exists($class) ){
				$this->Source = new $class;
			}
			else
				$this->registerWarning('definition class missing');
				$this->Source = new Snippets_Handler_Interpreter;
			}
			
			# Initiate required properties of the object
			$this->Source->inject($this->snippet, $code, $params);
			
			# Have it aquire data and fill gaps in the definition file
			$this->Source->aquire();
			
			# Get a general idea of the integrity of the definitions
			$integrity = $this->Source->validate();
			
		}
	
		public function getSnippet(){
			
			
			
		}
	
		public function initializeSnippet(){
			
			
			
		}
		
	}

?>