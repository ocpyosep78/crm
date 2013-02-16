<?php
	
	
	# Page validation error codes
	define('NAV_INVALID_FORMAT', 1);
	define('NAV_INVALID_ACCESS', 2);
	define('NAV_INVALID_HANDLER', 3);
	
	
	require_once(dirname(__FILE__).'/lib/Validation.lib.php');
	require_once(dirname(__FILE__).'/lib/Persistence.lib.php');
	
	
	class Navigation{
	
	
		private $uID;			# Currently loaded page's unique ID
		
		private $User;			# Represents currently logged in user
		private $Client;		# Represents client-side
	
	
		public function __construct(){
		
			$this->uID = isset($_GET['uID']) ? $_GET['uID'] : '';
			
			$this->User = Layers::get('User');
			$this->Client = Layers::get('Client');
			
			$this->Validation = new Navigation_Validation;
			$this->Peristence = new Navigation_Persistence;
		
		}
		
		public function getPage( $params ){
			
			
			
			$validation = $this->Validation->validate( $instance );
		
			# If use can't access page $page, abort navigation
			if( $this->User->cant($page) ){
				$msg = 'Su cuenta no posee permisos para acceder a esta página';
				return $this->Client->display( $msg );
			}
			
			$this->attemptFunctionCall() || $this->attemptAutomaticCall();
			
		}
	
	}