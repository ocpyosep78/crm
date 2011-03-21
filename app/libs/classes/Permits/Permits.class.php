<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/**
 * This class relies on SQL class for connecting to database
 * DB parameters are required when creating a Permits object (passed in an associative array)
 * It is not really portable anyway, so to depend on another class has no consequences
 */

	require_once( SQL_PATH.'/SQL.Permits.class.php' );
	
/**
 * Basically, Permits reads and stores all permissions, and processes permissions policies
 * It does not try to build permits untill used, to make it more efficient (does it on first demand)
 * All matters related to security and access permissions should go through this class
 */

	class Permits extends pSQL{
	
		private $SUPER_USER;	/* A user that has all permits always */
		private $SUPER_PROFILE;	/* A profile that has all permits always */
	
		private $user;
		private $profile;
		
		private $check;			/* Records the time when the object is built */
		private $timeOut;		/* Timeout for reloading permissions (compared with $check) */
		
		
		public function __construct(){	/* Connection parameters required */
		
			parent::__construct();
			
			# Initialize private properties
			$this->user = '';
			$this->profile = '';
			
			$this->SUPER_USER = NULL;
			$this->SUPER_PROFILE = NULL;
			
			$this->check = time();
			$this->timeOut = 60;
			
			$this->alias = array();
			
		}
		
/**
 * M A I N   P U B L I C   M E T H O D S
 */
		
		/**
		 * Force reloading of all permissions, modules and/or pages
		 */
		public function clear($what=NULL){
		
			foreach( array('permits', 'modules', 'pages', 'areas') as $item ){
				if( is_null($what) || $what == $item ) $this->cache($item, NULL);
			}
			
		}
		
		/**
		 * Alias one permission to another. Some tools in the app automatically check
		 * permissions based on some object's code. I.e. customerContacts, when processed by
		 * Lists object, will look for permissions such as 'editCustomerContacts', that's not
		 * a real permission. When List seeks for it, we should map the permission to permit
		 * 'editCustomers' (that is a real permit).
		 */
		public function setAlias($code, $alias){
			if( !is_array($alias) ) $this->alias[$alias] = $code;
			else foreach( $alias as $k ) $this->setAlias($code, $k);
		}
		
		/**
		 * Returns a boolean indicating if current user can access a certain page or feature.
		 * See Permits#setAlias' comment for more information on pseudo-permissions mapped to
		 * real permissions.
		 */
		public function can( $what ){
			$this->buildPermits();	/* Make sure we got the right permits to return */
			$permits =  $this->getPermits();
			return $this->isSuperUser()
				? true
				: isset($permits[isset($this->alias[$what]) ? $this->alias[$what] : $what]);
		}
		
		/**
		 * Shortcut to !Permits#can
		 */
		public function cant( $what ){	return !$this->can( $what );	}
		
		/**
		 * Normalized 'access restricted' message
		 */
		public function noAccessMsg(){
			return showStatus('Su cuenta no posee permisos para realizar esta acción.');
		}
		
		/**
		 * Checks if a permission is granted and dies with a global ¡access restricted¡ message
		 */
		public function stopIfNoPermission( $perm ){
			if( $this->cant($perm) ) die('Su cuenta no posee permisos para realizar esta acción.');
		}
		
		
/**
 * G E T T E R S
 */
		
		public function getPermits(){
			$this->buildPermits();	/* Make sure we got the right permits to return */
			return ($ret=$this->getCached('permits')) ? $ret : array();
		}
		
		public function getModules(){
			$this->buildModules();
			return ($ret=$this->getCached('modules')) ? $ret : array();
		}
		
		public function getPages(){
			$this->buildPages();
			return ($ret=$this->getCached('pages')) ? $ret : array();
		}
		
		public function getAreas(){
			$this->buildAreas();
			return ($ret=$this->getCached('areas')) ? $ret : array();
		}
		
		public function getModuleFromPage( $page ){
			$this->buildPages();
			$pages = $this->getCached('pages');
			return isset($pages[$page]) ? $pages[$page]['module'] : NULL;
		}
		
		public function getPagesFromModule( $mod ){
			$this->buildPages();
			$pages = $this->getCached('pages');
			foreach( $pages as $page ){
				if( $page['module'] == $mod || $page['id_area'] == 'global' ) $res[$page['code']] = $page;
			}
			return isset($res) ? $res : array();
		}
		
		
/**
 * S E T T E R S
 */
 
		public function setTimeOut( $secs=0 ){
			if( is_integer($secs) ) $this->timeOut = max($secs, 10);
		}
		public function setUser( $att=NULL ){
			$this->user = $att;
		}
		public function setProfile( $att=NULL ){
			$this->profile = $att;
		}
		public function setSuperUser( $att=NULL ){
			$this->SUPER_USER = $att;
		}
		public function setSuperProfile( $att=NULL ){
			$this->SUPER_PROFILE = $att;
		}
		
		
/**
 * P R I V A T E   M E T H O D S
 */
		
		private function buildPermits(){
		
			if( !$this->updateNeeded('permits') ) return;
			
			$permits = $this->isSuperUser()
				? parent::getPermits()
				: parent::getUserPermits($this->profile, $this->user);
			
			$this->cache('permits', $permits);
			$this->cache('permitsChecked', $this->check);
			
		}
		
		private function buildModules(){
		
			if( !$this->updateNeeded('modules') ) return;
			
			$modules = $this->isSuperUser()
				? parent::getModules()
				: parent::getUserModules($this->profile, $this->user);
			
			$this->cache('modules', $modules);
			$this->cache('modulesChecked', $this->check);
			
		}
		
		private function buildPages(){
		
			if( !$this->updateNeeded('pages') ) return;
			
			$pages = $this->isSuperUser()
				? parent::getPages()
				: parent::getUserPages($this->profile, $this->user);
			
			$this->cache('pages', $pages);
			$this->cache('pagesChecked', $this->check);
			
		}
		
		private function buildAreas(){
		
			if( !$this->updateNeeded('areas') ) return;
			
			$areas = parent::getAreas();
			
			$this->cache('areas', $areas);
			$this->cache('areasChecked', $this->check);
			
		}
		
		private function isSuperUser(){
			return ($this->user && $this->user == $this->SUPER_USER)
				|| ($this->profile && $this->profile == $this->SUPER_PROFILE);
		}
		
		private function isTimedOut( $what ){
			return $this->getCached($what.'Checked') < $this->check - $this->timeOut;
		}
		
		private function hasUserChanged(){
			if( !$this->user || !$this->profile ) return true;	/* No user is treated as user changed */
			return $this->user != $this->getCached('user') || $this->profile != $this->getCached('profile');
		}
		
		private function updateNeeded( $what ){
			return is_null($this->getCached($what)) || $this->isTimedOut($what) || $this->hasUserChanged();
		}
		
		
/**
 * C A C H E   H A N D L I N G
 */
		
		private function cache($where, $what=NULL){
			if( !$where || !$this->user || !$this->profile ) return;
			$_SESSION['Permits'][$where] = $what;
			$_SESSION['Permits']['user'] = $this->user;
			$_SESSION['Permits']['profile'] = $this->profile;
		}
		
		private function getCached($where, $default=NULL){
			return isset($_SESSION['Permits'][$where]) ? $_SESSION['Permits'][$where] : $default;
		}
		
	}

?>