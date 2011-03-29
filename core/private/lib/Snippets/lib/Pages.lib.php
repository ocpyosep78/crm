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
 *    constant SNIPPETS_ERROR_OUTPUT (always)
 */
	
	class Snippet_Pages{
	
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
				'reportTo'	=> SNIPPETS_ERROR_OUTPUT,
				'keys'		=> array(),
				'uID'		=> microtime(),
			);
		
		}
	
		/**
		 * string getSnippet ( string $snippet )
		 * @access: public
		 * 
		 * @overview: defines whether the requested snippet is single or
		 *            composed and handles the rest to #getSingleSnippet
		 *            or #getComposedSnippet, respectively, and forwards
		 *            whatever they return (HTML string expected).
		 *            Single snippet names are those starting with 'snp_'
		 * 
		 * @onError: this function doesn't handle errors
		 * 
		 * @returns: an HTML string
		 */
		public function getSnippet( $snippet ){
			
			return ($match = preg_match('/^snp_(.+)/', $snippet))
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
				case 'create':
				case 'edit':
					$list = array('comboList', $snippet);
					break;
				default:
					$list = (array)$snippet;
					break;
			}
			
			# Get each individual snippet and tie them in one piece of HTML
			$HTML = '';
			foreach( $list as $single ){
				$HTML .= $this->getSingleSnippet( $single );
			}
			
			return $HTML;
		
		}
		
		private function getSingleSnippet( $snippet ){
			
			# Get Handler, redirect error output on failure
			$Handler = $this->Handlers->getHandlerFor( $snippet );
			if( !$Handler ){
				$msg = "Snippets Pages: Handler not found for {$snippet}";
				# Output error through ajax layer's #display method
				if( $this->params['reportTo'] == 'status' ){
					$this->Layers->get('ajax')->display($msg, 'error');
					return '';
				}
				# Output error through the returned HTML string
				elseif( $this->params['reportTo'] == 'html' ){
					return $this->Layers->get('error')->Error( $msg );
				}
			}
			
			# Handler#start should cover most common actions to initialize
			$Handler->start($this->code, $this->params);
			$HTML = $Handler->getSnippet();
			
			# If writeTo param is set, it means we're expected to print
			# the resulting HTML through the ajax layer, and initialize
			# the result after it's printed (event handlers, checks, ties
			# to other snippets with same page_uID, etc.)
			if( $params['writeTo'] ){
				$this->Layers->get('ajax')->write($params['writeTo'], $HTML);
				$Handler->initializeSnippet();
			}
			
			return $HTML;
			
		}
		
	}

?>