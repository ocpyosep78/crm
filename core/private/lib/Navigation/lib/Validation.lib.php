<?php

/**
 * @overview: takes requests for new navigation instances, and
 *            performs required checks to see if it is going to
 *            succeed. I.e. if there is a function or method
 *            capable of creating a requested page, or whether
 *            the current user has access granted to such page.
 */

	class Navigation_Validation{
		
		private $User;		# Represents the logged user
		
		public function __construct(){
			
			$this->User = Layers::get('User');
			
		}
		
		public function validate( $instance ){
			
			if( !$this->validateFormat() ){
				return NAV_INVALID_FORMAT;
			}
			
			if( !$this->validateAccess() ){
				return NAV_INVALID_ACCESS;
			}
			
			if( !$this->validateHandler() ){
				return NAV_INVALID_HANDLER;
			}
			
			return true;
			
		}
		
	}

?>