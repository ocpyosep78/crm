<?php

/**
 * Methods related to site navigation and current coordinates (page and input atts).
 * Takes a snapshot with a unique code. Caches  name and paramters of pages to load,
 * and retrieves them to rebuild the page from it's code.
 */
class Nav
{

	private $code;
	private $atts;

	public $page;
	public $realPage;


	public function __construct()
	{
		$this->code = isset($_GET['nav']) ? $_GET['nav'] : NULL;
		$this->realPage = $this->page = $this->getSnapshot($this->code, 'page');
	}

	public function getCode()
	{
		return $this->code;
	}

	public function changeCode($code)
	{
		$this->code = $code;
	}

	public function clear()
	{
		$_SESSION['nav'] = array();
	}

	/**
	 * Override automatic detection of page and module, and load another page
	 * instead (that page's aliased to the one calling us)
	 */
	public function redirectTo($toPage, $atts=NULL, $msg='', $type=0)
	{
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
	public function getPage($page, $atts=array(), $msg='', $type=0)
	{
		if ($msg)
		{
			$this->queueMsg($msg, $type);
		}

		$code = $this->regNav($page, $atts);
		$href = "?nav={$code}";

		if ($code === true)
		{
			return $this->loadContent();
		}
		elseif (is_null($code))
		{
			$msg = 'La página solicitada no está disponible';
			throw new PublicException($page);
		}
		elseif (!$code)
		{
			$msg = 'No es posible cargar la página solicitada';
			throw new PublicException($page);
		}
		else
		{
			return addScript("location.href = '{$href}'");
		}
	}

	public function setJSParams()
	{
		addScriptCall('IniParams.set', func_get_args());
	}

	/**
	 * Reloads the page with the same atts.
	 * Optionally, it might show a status message after reloading
	 */
	public function reloadPage($msg='', $type=0)
	{
		$this->queueMsg($msg, $type);
		return $this->loadContent();
	}

	public function reloadPageWithAtts($atts, $msg='', $type=0)
	{
		$this->queueMsg($msg, $type);
		return $this->loadContent(NULL, is_array($atts) ? $atts : array($atts));
	}

	public function goBack($msg='', $type=0)
	{
		$this->queueMsg($msg, $type);
		return addScript('history.go(-1);');
	}

	/**
	 * Fill main_box (content box within main frame) with the right content
	 * Content might be given as html (parsed) or as a path to a template, in
	 * which case we fetch that template with Smarty.
	 */
	public function updateContent($page, $parsed=false)
	{
		$path = TEMPLATES_PATH . "/{$page}";

		if (!$parsed && !is_file($page) && !is_file($path))
		{
			return say('La página que intenta cargar no está disponible.');
		}

		$HTML = $parsed ? $page : Template::one()->fetch($page);

		return addAssign('main_box', 'innerHTML', $HTML);
	}

	/**
	 * Snapshot's already saved, and browser reloaded with a new navCode, then
	 * requested the real content of the page (within the frame). We call the
	 * page builder function (page_{$page}) and handle returned results.
	 */
	public function loadContent($code=NULL, $atts=NULL)
	{
		if (is_null($code))
		{
			$code = $this->code;
		}

		# Restore parameters from cache (or get default values)
		$page = $this->getSnapshot($code, 'page');

		# If atts is given, use that one instead (and register it)
		if (!is_null($atts))
		{
			$this->cache('atts', $atts);
		}
		else
		{
			$atts = $this->getSnapshot($code, 'atts');
		}

		# Make sure page exists and user can access it
		Access::enforce($this->checkPage($page));

		# Call page generation function with cached atts
		$res = call_user_func_array("page_{$page}", $atts);

		if (!$res)
		{
			$tpl = Access::pageArea($page)."/{$page}.tpl";

			if (is_file(TEMPLATES_PATH . "/{$tpl}"))
			{
				$this->updateContent($tpl);
			}
			else
			{
				$msg = "No se pudo encontrar la plantilla de la página ({$tpl})";
				throw new Exception($msg);
			}
		}

		addScriptCall('iniPage', $page);
		$this->processQueuedMsg();

		return oXajaxResp();
	}

	public function abortFrame($msg='', $code=0)
	{
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
	private function regNav($page, $atts=array())
	{
		if (!$this->checkPage($page))
		{
			return NULL;
		}

		# Check whether requested page is the same as current one, to avoid reload
		if ($page == $this->currentPage())
		{
			$this->cache('atts', $atts);

			return true;
		}
		else
		{
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
	private function checkPage($page)
	{
		require_once MODS_PATH . '/pages.php';
		return $page && Access::can($page) && is_callable("page_{$page}");
	}

	/**
	 * Retrieves the state of the program when another page was loaded, by its nav code.
	 * If that code is not found (most likely it belonged to an old terminated session),
	 * the default page is returned instead.
	 */
	public function getNav()
	{
		$navCode = !empty($_GET['nav']) ? $_GET['nav'] : '';
		$nav = $this->getCached('code');

		return empty($nav[$navCode])
			? array('page' => 'home', 'atts' => serialize([]))
			: array('page' => $nav[$navCode]['page'], 'atts' => $nav[$navCode]['atts']);
	}

	/**
	 * Generates a uniqueID for a nav code (the code of a particular snapshot).
	 */
	public function genNavCode()
	{
		return str_replace(' ', '', microtime() . rand(1, time()));
	}

	/**
	 * Caches a var-value pair within current snapshot
	 */
	private function cache($what, $how=NULL)
	{
		$code = $this->code ? $this->code : '';
		$what && ($_SESSION['nav'][$code][$what] = $how);
	}

	/**
	 * Returns a cached var (usually a snapshot)
	 */
	private function getCached($what)
	{
		return isset($_SESSION['nav'][$what]) ? $_SESSION['nav'][$what] : NULL;
	}

	/**
	 * Pretty much like getCache, but returns a particular snapshot or an element of it,
	 * instead of requesting a snapshot by code and then taking one part or another.
	 */
	public function getSnapshot($code=NULL, $what=NULL)
	{
		return ($snapshot = $this->getCached($code ? $code : $this->code))
			? ($what ? (isset($snapshot[$what]) ? $snapshot[$what] : NULL) : $snapshot)
			: ($what == 'page' ? 'home' : []);
	}

	/**
	 * Just a fast method to get the current module
	 */
	public function getCurrentModule()
	{
		return Access::pageArea($this->currentPage());
	}

	/**
	 * Just a fast method to get the current page
	 */
	public function currentPage()
	{
		return $this->getSnapshot(NULL, 'page');
	}

	/**
	 * Just a fast method to get the current module
	 */
	public function getCurrentAtts()
	{
		return $this->getSnapshot(NULL, 'atts');
	}

	/**
	 * Queue messages to be shown on next page load (with loadContent)
	 */
	public function queueMsg($msg='', $type=0)
	{
		is_string($msg) || ($msg = '');
		regSes('queuedMsg', array('msg' => $msg, 'type' => $type));
	}

	/**
	 * If there's a queued message, show it (ajax) or return it (regular call)
	 */
	public function processQueuedMsg()
	{
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