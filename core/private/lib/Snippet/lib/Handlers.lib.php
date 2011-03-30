<?php

/**
 * DEPENDS ON:
 * 
 *    class Handler (one of its children is dinamically created, on success)
 *        #__construct( string $snippet )
 *    constant SNIPPET_HANDLERS_PATH (always)
 */
 
 
  	require_once(dirname(__FILE__).'/Handler_Defaults.lib.php');
  	require_once(dirname(__FILE__).'/Handler_Interpreter.lib.php');
 	require_once(dirname(__FILE__).'/Handler_Commons.lib.php');
 
 	
	class Snippet_Handlers{
	
		/**
		 * object getHandlerName( string $snippet )
		 * @access: private
		 * 
		 * @overview: directs each type of snippet to the right handler
		 *            The list and mapping can be editted/extended as it
		 *            fits the adition, edition or removal of snippets
		 *            from the Snippets library.
		 */
		private function getHandlerName( $snippet ){
		
			switch( $snippet ){
				case 'comboList':
				case 'bigTools':
					return 'widgets';
				case 'simpleList':
				case 'commonList':
				case 'innerCommonList':
					return 'lists';
				case 'create':
				case 'edit':
				case 'view':
					return 'items';
				case 'axn_edit':
				case 'axn_block':
				case 'axn_unblock':
				case 'axn_delete':
					return 'actions';
				default:
					return NULL;
			}
			
		}
	
		/**
		 * object getHandlerFor( string $snippet )
		 * @access: public
		 * 
		 * @overview: asks #getHandlerName for the right Handler to manage
		 *            the requested snippet's creation, and checks the
		 *            integrity of the request. If everything's in place, it
		 *            returns a Handler object
		 */
		public function getHandlerFor( $snippet ){
		
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

?>