<?php
	
	require_once(dirname(__FILE__).'/layers/User.layer.php');
	require_once(dirname(__FILE__).'/layers/Client.layer.php');


	class Navigation{
	
	
		private $uID;			# Currently loaded page's unique ID
		
		private $User;			# Represents currently logged in user
		private $Client;		# Represents client-side of the application
	
	
		public function __construct(){
		
			$this->uID = isset($_GET['uID']) ? $_GET['uID'] : '';
			
			$this->User = new Navigation_Layer_User;
			$this->Client = new Navigation_Layer_Client;
		
		}
		
		public function getPage( $params ){
		
			# If use can't access page $page, abort navigation
			if( $this->User->cant($page) ){
				$msg = 'Su cuenta no posee permisos para acceder a esta página';
				return $this->Client->display( $msg );
			}
			
			$this->attemptFunctionCall() || $this->attemptAutomaticCall();
				
			}
			
		}
	
	}


?>