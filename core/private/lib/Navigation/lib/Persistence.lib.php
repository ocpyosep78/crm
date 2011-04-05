<?php

/**
 * @overview: handles the navigation history stack. It provides two
 *            public methods only:
 *
 *            #push: inserts a new instance in the stack
 *                @params: mixed $instance
 *                @returns: a unique ID for this element
 *
 *            #pull: retrieves an entry from the stack
 *                @params: string $uID
 *                @returns: an instance on success, NULL otherwise
 *
 *            
 */

	define('NAV_PERSIST', 'App_Template_Navigation');

	class Navigation_Persistence{
		
		private $stack;
		
		/**
		 * @overview: at contruct, retrieve (or initialize) recorded
		 *            navigation history, and clear the stack beyond
		 *            30th element.
		 */
		public function __construct(){
			
			isset($_SESSION[NAV_PERSIST])
				|| $_SESSION[NAV_PERSIST] = array();
				
			$this->stack = &$_SESSION[NAV_PERSIST];
			
			while( count($this->stack) > 30 ){
				array_pop( $this->stack );
			}
			
		}
		
		public function push( $instance ){
			
			$uID = time().microtime();
			
			$this->stack[$uID] = $instance;
			
			return $uID;
			
		}
		
		public function pull( $uID ){
			
			return isset($this->stack[$uID])
				? $this->stack[$uID]
				: NULL;
			
		}
		
	}

?>