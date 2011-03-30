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
	
	class Snippet_Pages{
	
		private $snippet;
	
		private $Layers;
		private $Handlers;
		
		private $code;
		private $params;
	
		public function __construct($code, $params){
		
			$this->Layers = new Snippet_Layers;
			$this->Handlers = new Snippet_Handlers;
			
			$this->code = $code;
			
			# Fill params with default values
			$this->params = $params + array(
				'modifier'	=> NULL,
				'writeTo'	=> NULL,
				'keys'		=> array(),
				'page_uID'	=> microtime(),
			);
		
		}
	
		/**
		 * string getSnippet ( string $snippet )
		 * @access: public
		 * 
		 * @overview: defines what kind of snippet has been requested and
		 *            forwards execution to the right method. Possible
		 *            kinds are single snippet (HTML), composed snippet
		 *            (HTML) and actions. In any case, HTML is expected
		 *            and #getSnippet forwards it (besides ajax actions
		 *            any of them take in between).
		 * 
		 * Single snippet names are those starting with 'snp_'
		 * Action snippet names are those starting with 'axn_'
		 * All other names are understood as composed snippets.
		 * 
		 * @onError: this function doesn't handle errors
		 * 
		 * @returns: an HTML string
		 */
		public function getSnippet( $snippet ){
		
			return preg_match('/snp_(.+)/', $snippet, $match)
				? $this->getSingleSnippet( $match[1] )
				: $this->getComposedSnippet( $snippet );
			
		}
	
		/**
		 * private string getComposedSnippet( string $snippet )
		 * @access: public
		 * 
		 * @overview: defines whether the requested snippet is single or
		 *            composed and handles the rest to #getSingleSnippet
		 *            or #getComposedSnippet, respectively, and forwards
		 *            whatever they return (HTML string expected)
		 * 
		 * @onError: this function doesn't handle errors
		 * 
		 * @returns: an HTML string
		 */
		private function getComposedSnippet( $snippet ){
			
			# This is actually where composed snippets are defined
			# For each code it might or might not add other snippets after
			# or before the main one, to build a composed snippet (usually
			# meant to be a whole working page)
			switch( $snippet ){
				case 'commonList':
					$list = array('bigTools', 'comboList', 'commonList');
					break;
				case 'info':
				case 'edit':
					$list = array('comboList', $snippet);
					break;
				case 'view':
					$list = array('info');
				case 'doEdit':
				case 'block':
				case 'unblock':
				case 'delete':
				default:
					$list = (array)$snippet;
					break;
			}
			
			# Set writeTo param to NULL to avoid each snippet being printed
			# and initialized on its own
			$writeTo = $this->params['writeTo'];
			$this->params['writeTo'] = NULL;
			
			# Get each individual snippet and tie them in one piece of HTML
			$HTML = '';
			foreach( $list as $snippet ){
				$HTML .= $this->getSingleSnippet( $snippet );
			}
			
			# Print composed HTML and run snippet (if writeTo param not empty)
			if( $writeTo ){
				$this->printSnippet($writeTo, $HTML);
				foreach( $list as $snippet ) $this->runSnippet( $snippet );
			}
			
			return $HTML;
		
		}
		
		private function getSingleSnippet( $snippet ){
			
			$code = $this->code;
			$params = $this->params;
			
			# Get Handler, redirect error output on failure
			$Handler = $this->Handlers->getHandlerFor( $snippet );
			if( !$Handler ){
				$msg = "Snippets Pages: Handler not found for {$snippet}";
				return Snippet_Tools::issueWarning( $msg );
			}
			
			# Initialize Handler and get HTML for this snippet
			$HTML = $Handler->start($code, $params)->getSnippet();
			
			# If writeTo param is set, it means we're expected to print
			# the resulting HTML through the ajax layer, and initialize
			# the result after it's printed (event handlers, checks, ties
			# to other snippets with same page_uID, etc.)
			if( $this->params['writeTo'] ){
				$this->printSnippet($this->params['writeTo'], $HTML);
				$this->runSnippet( $snippet );
			}
			
			return $HTML;
			
		}
	
		private function printSnippet($target, $HTML){
		
			empty($target)
				|| $this->Layers->get('ajax')->write($target, $HTML);
			
		}
	
		private function runSnippet( $snippet ){
			
			$code = $this->code;
			$params = Snippet_Tools::toJson( $this->params );
			
			$cmd = "Snippet.initialize('{$snippet}', '{$code}', {$params});";
			
			$this->Layers->get('ajax')->addScript( $cmd );
			
		}
		
	}

?>