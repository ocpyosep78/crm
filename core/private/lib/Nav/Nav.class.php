<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/**
 * Methods related to site navigation and current coordinates (page and input atts).
 * Takes a snapshot with a unique code. Caches  name and paramters of pages to load,
 * and retrieves them to rebuild the page from it's code.
 */

	class Nav{

		/**
		 * TEMP : getPage for Snippet, while navigation updates to support Snippets
		 */
		public function getSnippet($snippet, $mod, $params, $msg='', $type=NULL){

			if( $msg ) $this->queueMsg($msg, $type);

			$atts = array('snippet' => $snippet, 'mod' => $mod, 'params' => $params);

			$detail = DEVMODE ? " (snippet: {$snippet})" : '';

			switch( $snippet ){
				case 'commonList':
					$page = $code;
				case 'createItem':
					$page = 'create'.ucfirst($code);
				case 'editItem':
					$page = 'edit'.ucfirst($code);
				case 'viewItem':
					$page = "{$code}Info";
				default:
					return say("La página solicitada no está disponible{$detail}.");
			}

			if( !oPermits()->can($page) ){
				return say("Su cuenta no posee permisos para acceder a esta página{$detail}.");
			}

			$this->code = time().microtime();
			$this->cache('page', $page);
			$this->cache('atts', $atts);

			return addScript("location.href = '?nav={$this->code}'");

		}

		public function loadSnippet(){

		}

		private $code;
		private $atts;

		public $page;
		public $realPage;

		public $inFrame;			/* Set to true by #showPage, false otherwise */



		public function __construct(){

			$this->code = isset($_GET['nav']) ? $_GET['nav'] : NULL;
			$this->realPage = $this->page = $this->getSnapshot($this->code, 'page');

			$this->inFrame = isset($_GET['iframe']);

		}
		public function getCode()			{	return $this->code;		}
		public function changeCode( $code )	{	$this->code = $code;	}

		public function clear(){
			$_SESSION['nav'] = array();
		}

		/**
		 * Override automatic detection of page and module, and load another page
		 * instead (that page's aliased to the one calling us)
		 */
		public function redirectTo($toPage, $atts=NULL, $msg='', $type=0){

			$this->queueMsg($msg, $type);

			if( !is_null($atts) && !is_array($atts) ) $atts = array( $atts );

			$this->cache('page', $toPage);

			return $this->loadContent(NULL, $atts);

		}

		/**
		 * Register a new snapshot and redirect user by giving him the new navCode.
		 * An important exception: when a page needs not reloading (same page, maybe
		 * different atts), we just deliver content at this first call.
		 */
		public function getPage($page, $atts=array(), $msg='', $type=0, $inFrame=false){
			if( $msg ) $this->queueMsg($msg, $type);

			$code = $this->regNav($page, $atts);
			$href = "?nav={$code}"
				.(getSkinName() ? "&skin=".getSkinName() : '')
				.($inFrame || $this->inFrame ? '&iframe' : '');

			if( $code === true ){
				loadMainSmartyVars();
				require_once(CORE_PRIVATE.'pageMgr.php');
				return $this->loadContent();
			}
			elseif( $code === NULL ){
				return say("La página solicitada no está disponible.");
			}
			elseif( !$code ){
				return say("No es posible cargar la página solicitada.");
			}
			elseif( !$inFrame ){
				return addScript("location.href = '{$href}'");
			}
			else return $href;	/* Just for self#showPage */

		}

		/**
		 * Loads a page within an iframe
		 */
		public function showPage($page, $atts=array(), $msg='', $type=0)
		{
			$href = $this->getPage($page, $atts, $msg, $type, true);

			# Object returned by #getPage could be an code(correct) or a say() call (error)
			if (is_string($href))
			{
				return addScript("Frames.loadPage('{$href}');");
			}

			$this->inFrame = false;
			return $href;	// Not a real href but a xajax call to say()
		}

		public function setJSParams(){

			addScriptCall('IniParams.set', func_get_args());

		}

		/**
		 * Reloads the page with the same atts.
		 * Optionally, it might show a status message after reloading
		 */
		public function reloadPage($msg='', $type=0){

			$this->queueMsg($msg, $type);
			return $this->loadContent();

		}

		public function reloadPageWithAtts($atts, $msg='', $type=0){

			$this->queueMsg($msg, $type);
			return $this->loadContent(NULL, is_array($atts) ? $atts : array($atts));

		}

		public function goBack($msg='', $type=0){

			$this->queueMsg($msg, $type);
			return addScript('history.go(-1);');

		}

		/**
		 * Fill main_box (content box within main frame) with the right content
		 * Content might be given as html (parsed) or as a path to a template, in
		 * which case we fetch that template with Smarty.
		 */
		public function updateContent($page, $parsed=false){

			if( !$parsed && !is_file($page) && !is_file(TEMPLATES_PATH.$page) ){
				return $this->inFrame
					? $this->abortFrame('La página que intenta cargar no está disponible.')
					: say('La página que intenta cargar no está disponible.');
			}

			$HTML = $parsed ? $page : oSmarty()->fetch($page);

			return addAssign('main_box', 'innerHTML', $HTML);

		}

		/**
		 * Snapshot's already saved, and browser reloaded with a new navCode, then
		 * requested the real content of the page (within the frame). We call the
		 * page builder function (page_{$page}) and handle returned results.
		 */
		public function loadContent($code=NULL, $atts=NULL){
            oSmarty()->assign('inFrame', $this->inFrame);

			if( is_null($code) ) $code = $this->code;

			# Restore parameters from cache (or get default values)
			$page = $this->getSnapshot($code, 'page');

			# If atts is given, use that one instead (and register it)
			if( !is_null($atts) ) $this->cache('atts', $atts);
			else $atts = $this->getSnapshot($code, 'atts');

			# Make sure page exists and user can access it
			if( !$this->checkPage($page) ) return oPermits()->noAccessMsg();

			# Call page generation function with cached atts
			$res = call_user_func_array("page_{$page}", $atts);

			if( !$res ){
				$tpl = oPermits()->getModuleFromPage($page)."/{$page}.tpl";
				if( is_file(TEMPLATES_PATH.$tpl) ) $this->updateContent( $tpl );
				else trigger_error("No se pudo encontrar la plantilla de la página ({$tpl})");
			}

			addScriptCall('iniPage', $page);
			$this->processQueuedMsg();

			return oXajaxResp();

		}

		public function abortFrame($msg='', $code=0){

			return addScript("Frames.close('{$msg}', {$code});");

		}

		/**
		 * Save a snapshot of currently requested page (name and parameters)
		 * When user navigates through browser navigation, we'll associate the code
		 * with this snapshot.
		 * In cases were a page is reloaded (just maybe with different atts), there is
		 * no need to reload the whole page. We return TRUE meaning just the content
		 * needs updating.
		 */
		private function regNav($page, $atts=array()){

			if( !$this->checkPage($page) ) return NULL;

			# Check whether requested page is the same as current one, to avoid reload
			if( $page == $this->getCurrentPage() ){
				$this->cache('atts', $atts);
				return true;
			}
			else{
				$this->code = $this->genNavCode();
				$this->cache('page', $page);
				$this->cache('atts', $atts);
				return $this->code;
			}

		}

		/**
		 * Before registering a new snapshot and redirecting the user to the new page, we
		 * make sure the page actually exists and is accesible by the current user.
		 */
		private function checkPage( $page ){

			if( !$page || !oPermits()->can($page) ) return false;

			$mod = oPermits()->getModuleFromPage( $page );
			$file = MODS_PATH."{$mod}/pages.php";
			if( !is_file($file) || !require_once($file) ) return false;

			return function_exists("page_{$page}");

		}

		/**
		 * Retrieves the state of the program when another page was loaded, by its nav code.
		 * If that code is not found (most likely it belonged to an old terminated session),
		 * the default page is returned instead.
		 */
		public function getNav(){

			$navCode = !empty($_GET['nav']) ? $_GET['nav'] : '';
			$nav = $this->getCached('code');

			return empty($nav[$navCode])
				? array('page' => DEFAULT_PAGE, 'atts' => DEFAULT_PAGE_ATTS)
				: array('page' => $nav[$navCode]['page'], 'atts' => $nav[$navCode]['atts']);

		}

		/**
		 * Generates a uniqueID for a nav code (the code of a particular snapshot).
		 */
		public function genNavCode(){

			return time().microtime();

		}

		/**
		 * Caches a var-value pair within current snapshot
		 */
		private function cache($what, $how=NULL){
			if( $what ) $_SESSION['nav'][$this->code ? $this->code : ''][$what] = $how;
		}

		/**
		 * Returns a cached var (usually a snapshot)
		 */
		private function getCached( $what ){
			return isset($_SESSION['nav'][$what]) ? $_SESSION['nav'][$what] : NULL;
		}

		/**
		 * Pretty much like getCache, but returns a particular snapshot or an element of it,
		 * instead of requesting a snapshot by code and then taking one part or another.
		 */
		public function getSnapshot($code=NULL, $what=NULL){
			return ($snapshot = $this->getCached($code ? $code : $this->code))
				? ($what ? (isset($snapshot[$what]) ? $snapshot[$what] : NULL) : $snapshot)
				: ($what == 'page' ? DEFAULT_PAGE : unserialize(DEFAULT_PAGE_ATTS));
		}

		/**
		 * Just a fast method to get the current module
		 */
		public function getCurrentModule(){
			return oPermits()->getModuleFromPage( $this->getCurrentPage() );
		}

		/**
		 * Just a fast method to get the current page
		 */
		public function getCurrentPage(){
			return $this->getSnapshot(NULL, 'page');
		}

		/**
		 * Just a fast method to get the current module
		 */
		public function getCurrentAtts(){
			return $this->getSnapshot(NULL, 'atts');
		}

		/**
		 * Queue messages to be shown on next page load (with loadContent)
		 */
		public function queueMsg($msg='', $type=0){
			is_string($msg) || ($msg = '');
			regSes('queuedMsg', array('msg' => $msg, 'type' => $type));
		}

		/**
		 * If there's a queued message, show it (ajax) or return it (regular call)
		 */
		public function processQueuedMsg(){
			if (!isset($_SESSION['queuedMsg']))
			{
				$this->queueMsg();
			}

			$queued = getSes('queuedMsg');
			$this->queueMsg();		/* Clear session var after using it once */

			$msg = addslashes($queued['msg']);

			if ($msg)
			{
				oPageCfg()->add_jsOnLoad( "say('{$msg}', '{$queued['type']}');" );
				return say($msg, $queued['type']);
			}
		}

	}