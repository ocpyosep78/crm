<?php

/**
 * DEPENDS ON:
 * 
 *    class Snippet_Layers (always)
 *        layer ajax, for #addSnippet (on error and on $params['writeTo'])
 *            #display(string $msg, 'error')
 *            #write(string $writeTo, string $HTML)
 *        layer error, for #addSnippet (on error)
 *            #Error(string $msg)
 *    class Snippet_Handlers (always)
 *        #getHandlerFor( string $snippet )
 *    class Handler (its children are dinamically called)
 *        #start(string $code, array $params)
 *        #getSnippet()
 *        #initializeSnippet()
 *    constant SNIPPET_ERROR_OUTPUT (always)
 */	
 
 
  	require_once(dirname(__FILE__).'/Handler_Defaults.lib.php');
  	require_once(dirname(__FILE__).'/Handler_Source.lib.php');
 	require_once(dirname(__FILE__).'/Handler_Commons.lib.php');
	
	
	
	class Snippet_Initialize{
	
		private $snippet;
	
		private $Layers;
		
		private $code;
		private $params;
	
		public function __construct($code, $params){

			$this->Layers = new Snippet_Layers;
			
			$this->code = $code;
			
			$this->fillUnsetParams( $params );
			$this->params = $params;
		
		}
		
		
/**********************************************/
/****************** BEHAVIOR ******************/
/**********************************************/
	
		/**
		 * void fillUnsetParams( array $params )
		 * @access: private
		 * @overview: fills $params with default keys for those
		 *            items that are not set
		 * @returns: void
		 */
		private function fillUnsetParams( &$params ){
			
			# Fill params with default values
			$params += array(
				'modifier'		=> NULL,
				'filters'		=> array(),
				'group_uID'		=> microtime(),
				'writeTo'		=> NULL,
				'initialize'	=> !empty($params['writeTo']),
				'main'			=> NULL,
			);
			
		}
	
		/**
		 * array getComposedList( string $snippet )
		 * @access: private
		 * @overview: given a snippet code, define the list of individual
		 *            snippets that go together (plus the requested snippet
		 *            itself), in the order they're expected to be loaded
		 * @returns: a list of snippet codes (strings)
		 * @notes: $snippet is passed by reference, to be able to alias
		 */
		private function getComposedList( &$snippet ){
		
			switch( $snippet ){
				case 'listItem':
					$snippet = 'commonList';
				case 'commonList':
				case 'simpleList':
				case 'viewItem':
				case 'editItem':
//				case 'deleteItem':
					return array('bigTools', 'comboList', $snippet);
				case 'createItem':
					return array('bigTools', 'comboList', 'intro', $snippet);
				default:
					# Snippet's that don't merge with other snippets
					return (array)$snippet;
			}
			
		}
	
		/**
		 * string getHandlerName( string $snippet )
		 * @access: private
		 * @overview: directs each type of snippet to the right handler. The
		 *            list and mapping can be editted/extended, provided the
		 *            referred snippets exist
		 * @returns: a Handler's name
		 */
		private function getHandlerName( $snippet ){
		
			switch( $snippet ){
				case 'comboList':
				case 'bigTools':
				case 'intro':
					return 'widgets';
				case 'simpleList':
				case 'commonList':
				case 'innerCommonList':
				case 'complexList':
					return 'lists';
				case 'createItem':
				case 'editItem':
#test( array('snippet' => $snippet, 'code' => $this->code) + $this->params );
				case 'viewItem':
					return 'items';
				case 'block':
				case 'unblock':
test( array('snippet' => $snippet, 'code' => $this->code) + $this->params );
				case 'editField':
				case 'edit':
				case 'create':
				case 'delete':
				case 'deleteItem':	/* TEMP : alias of delete for now, back in items later */
					return 'actions';
				default:
                    // TODO : move this to somewhere more appropriate
                    if ($snippet == 'newCustomerTechItem') {
                        oNav()->getPage('createTechVisits', array(NULL, $this->params['filters']));
                        return NULL;
                    }elseif ($snippet == 'newAgendaEventItem') {
                        oNav()->getPage('createEvent', array(NULL, $this->params['filters']));
                        return NULL;
                    }
/* test( array('snippet' => $snippet, 'code' => $this->code) + $this->params );
Array(
    [snippet] => newCustomerTechItem
    [code] => customers
    [modifier] => customers
    [writeTo] =>
    [filters] => 129
    [group_uID] => 0.09624400 1324947334
    [initialize] =>
    [main] => newCustomerTechItem
) */
					return NULL;
			}
			
		}
		
		
/**********************************************/
/******************** CORE ********************/
/**********************************************/

		/**
		 * string getSnippet ( string $snippet )
		 * @access: public
		 * @overview: defines what kind of snippet has been requested and
		 *            forwards execution to the right method. Possible
		 *            kinds are single snippet (HTML), composed snippet
		 *            (HTML) and actions. In any case, HTML is expected
		 *            and #getSnippet forwards it (besides ajax actions
		 *            any of them take in between).
		 * Single snippet names are those starting with 'snp_'
		 * Action snippet names are those starting with 'axn_'
		 * All other names are understood as composed snippets.
		 * @onError: this function doesn't handle errors
		 * @returns: an HTML string
		 */
		public function getSnippet( $snippet ){
		
			if( preg_match('/snp_(.+)/', $snippet, $match) ){
				$this->params['group_uID'] = NULL;
				return $this->getSingleSnippet( $match[1] );
			}
			else{
				return $this->getComposedSnippet( $snippet );
			}
			
		}
	
		/**
		 * private string getComposedSnippet( string $snippet )
		 * @access: private
		 * @overview: defines whether the requested snippet is single or
		 *            composed. If composed, call #getSingleSnippet for
		 *            each component, preventing printing and initializing
		 *            untill all are retrieved. Then merge all components'
		 *            HTML.
		 *            if writeTo param, print the resulting HTML
		 *            if initialize param, initialize each individual piece
		 * @returns: an HTML string
		 */
		private function getComposedSnippet( $snippet ){
		
			# This is actually where composed snippets are defined
			# For each code it might or might not add other snippets after
			# or before the main one, to build a composed snippet (usually
			# meant to be a whole working page)
			$list = $this->getComposedList( $snippet );
			
			# Record main snippet for composed HTML
			$this->params['main'] = $snippet;
						
			# Clear writeTo param to avoid each snippet being printed
			$writeTo = $this->params['writeTo'];
			$this->params['writeTo'] = NULL;
			
			# Clear initialize param to stop snippets from being initialized
			$initialize = $this->params['initialize'];
			$this->params['initialize'] = false;
			
			# Get each individual snippet and tie them in one piece of HTML
			$HTML = '';
			foreach( $list as $snp ){
				$HTML .= $this->getSingleSnippet($snp);
			}
			
			# Print composed HTML and run snippet (if writeTo param not empty)
			!$writeTo || $this->printSnippet($writeTo, $HTML);
			
			# Initialize snippets (client-side, through ajax) if requested
			if( $initialize ) foreach( $list as $snp ) $this->runSnippet($snp);
			
			return $HTML;
		
		}
		
		/**
		 * string getSinlgeSnippet( string $snippet )
		 * @access: private
		 * @overview: given a snippet code, retrieve its HTML
		 *            if writeTo param, call #printSnippet
		 *            if initialize param, call #runSnippet
		 * @returns: an HTML string
		 */
		private function getSingleSnippet( $snippet ){
			
			$code = $this->code;
			$params = $this->params;
			
			# Get Handler, redirect error output on failure
			$Handler = $this->getHandlerFor( $snippet );
			if( !$Handler ){
				$msg = "Snippets Initialize: Handler not found for {$snippet}";
				return Snippet_Tools::issueWarning( $msg );
			}
			
			# Initialize Handler and get HTML for this snippet
			$HTML = $Handler->start($code, $params)->getSnippet();
			
			# If writeTo param is set, it means we're expected to print
			# the resulting HTML through the ajax layer
			if( $this->params['writeTo'] ){
				$this->printSnippet($this->params['writeTo'], $HTML);
				# If not empty, writeTo param forces initialization
				$this->params['initialize'] = true;
			}
			# Some snippets require initialization (client-side): adding
			# event handlers, checks, ties to other snippets, etc.
			!$this->params['initialize'] || $this->runSnippet($snippet);
			
			return $HTML;
			
		}
		
		/**
		 * void printSnippet( string $tgt , string $HTML )
		 * @access: private
		 * @overview: Given a DOM element's ID, print HTML inside it
		 * @returns: void
		 */
		private function printSnippet($tgt, $HTML){
		
			empty($tgt) || $this->Layers->get('ajax')->write($tgt, $HTML);
			
		}
		
		/**
		 * void runSnippet( string $snippet )
		 * @overview: Call snippet's initialize method (client side), passing
		 *            all relevant parameters, through the ajax layer
		 * @returns: void
		 */
		private function runSnippet( $snippet ){
			
			$code = $this->code;
			$params = Snippet_Tools::toJson( $this->params );
			
			$cmd = "Snippet.initialize('{$snippet}', '{$code}', {$params});";
			
			$this->Layers->get('ajax')->addScript( $cmd );
			
		}
		
		
/**********************************************/
/****************** HANDLERS ******************/
/**********************************************/
	
		/**
		 * object getHandlerFor( string $snippet )
		 * @access: private
		 * @overview: asks #getHandlerName for the right Handler to manage
		 *            the requested snippet's creation, and checks the
		 *            integrity of the request. If everything's in place, it
		 * @returns: a Handler object
		 */
		private function getHandlerFor( $snippet ){
		
			# Get name
			$hndName = $this->getHandlerName( $snippet );
			if( !$hndName ) return NULL;
			
			# Get class path and name
			$path = SNIPPET_HANDLERS_PATH."/{$hndName}.hnd.php";
			$class = "Snippet_hnd_{$hndName}";
			
			# Check path and class are right
			if( is_file($path) ) require_once( $path );
			if( !class_exists($class) ) return NULL;
			
			return new $class( $snippet );
			
		}
		
	}