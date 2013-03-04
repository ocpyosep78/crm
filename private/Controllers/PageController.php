<?php

class PageController extends Controller
{


	/**
	 * Directive ALLOW_UNLESS_DENIED means all public methods can be called from
	 * the client, if defined as pages. Use with care.
	 */
	const DENY_UNLESS_ALLOWED = 0;  // Pages are allowed by default
	const ALLOW_UNLESS_DENIED = 1;  // Pages are blocked by default


	/**
	 * All info relevant to current page (array).
	 */
	private static $current;


	/**
	 * protected static void content()
	 *      Refresh content with a new page, through ajax.
	 *
	 * NOTE : will trigger a page reload to force login, if disconnected.
	 *
	 * @return void
	 */
	protected static function content()
	{
		if (self::logged())
		{
			$args = self::readAjaxArgs();

			$pageid = self::requestedPage($args[0]['page']);
			self::setParams($pageid, $args[1]);

			Response::content(self::html(true), true);

			self::triggerContentLoad();
		}
		else
		{
			// Force page reload when the user has been disconnected
			Response::reload();
		}
	}


	/**
	 * protected static void page()
	 *      Interpret input to get requested page, validate it and print the
	 * full page or just the content, depending on the 'content' switch.
	 *
	 * @return void
	 */
	protected static function page()
	{
		if (self::logged())
		{
			// Translate args
			$_GET += ['args' => ''];
			$args = explode('/', trim($_GET['args'], '/')) + [NULL, NULL];

			$pageid = self::requestedPage($args[0]);

			// If input cannot be interpreted, use homepage instead
			if (!$pageid)
			{
				Response::say('No se encontró la página solicitada');
				$pageid = self::homepage();
			}
			// If user has no access to selected page, use homepage instead
			elseif (!Access::canLoad($pageid))
			{
				$msg = devMode()
					? 'La página solicitada está aun En Construcción'
					: 'Tu cuenta no tiene acceso a la página solicitada';
				Response::say($msg);

				$pageid = self::homepage();
			}

			// Store all info relevant to current page, as @@current
			self::setParams($pageid, $args[1]);

			self::triggerContentLoad();
		}

		echo self::html(isset($_GET['contentonly']));

		exit(0);
	}

	/**
	 * private string html([bool $onlyContent = false])
	 *      Build and return the HTML for the page.
	 *
	 * @return string
	 */
	private static function html($onlyContent=false)
	{
		// A snapshot of the current page state (group, area, page, etc)
		Template::one()->assign('pagestate', self::$current);

		// Logout button (navbar and menu)
		Template::one()->assign('img_logout', URL_IMAGES . '/logout.png');

		// Import a few constants into the javascript global scope
		Response::importConst('BBURL', 'IMAGES_URL');

		// Developer's mode
		Response::assign('DEVMODE', devMode());

		// Content
		if (!self::logged())
		{
			// No content is to be shown for guests, other than the login screen
			$content = Template::one()->fetch(PATH_TPLS . '/login.tpl');
		}
		else
		{
			// Import all @@current keys: 'page', 'atts', 'tree', 'handler'
			extract(self::$current);

			// Show menu for all pages unless explicitely told otherwise
			Response::showMenu();

			if (devMode() && !is_callable(self::handler($page['id'])))
			{
				$msg = "Handler {$page['model']}:{$page['action']} not callable";
				throw new Exception($msg);
			}

			switch (count($atts))
			{
				case 0: $handler();
					break;
				case 1: $handler($atts[0]);
					break;
				case 2: $handler($atts[0], $atts[1]);
					break;
				case 3: $handler($atts[0], $atts[1], $atts[2]);
					break;
				default: call_user_func_array($handler, $atts);
					break;
			}

			$tpl = "{$page['model']}/{$page['action']}.tpl";
			$path = TEMPLATES_PATH . "/{$tpl}";

			if (!is_file($path))
			{
				$content = "No se ha encontrado la plantilla ({$tpl})";
			}
			else
			{
				$content = Template::one()->fetch($path);
			}
		}

		Template::one()->assign('content', $content);

		if (!$onlyContent)          // Full page
		{
			// If there is a skin sellection apply it now, else use defaults
			$skin = getSes('skin') ? URL_SKINS . "/{$skin}" : NULL;
			$tpl  = $skin ? "{$skin}.tpl" : PATH_TPLS . '/main.tpl';
			$css  = $skin ? "{$skin}.css" : URL_STYLES . '/style.css';

			Template::one()->assign('css', $css);
		}
		elseif (self::logged())     // Only content
		{
			// Update navbar and menu if this page has got a module
			if (self::$current['page']['module'])
			{
				$navhtml = Template::one()->fetch(PATH_TPLS . '/navbar.tpl');
				Response::html('#main_navBar:parent', $navhtml);

				$menuhtml = Template::one()->fetch(PATH_TPLS . '/menu.tpl');
				Response::html('#main_menu:parent', $menuhtml);
			}

			// Simple wrapper, mainly for javascript plus the content's html
			$tpl = PATH_TPLS . '/content.tpl';
		}

		return Template::one()->fetch($tpl);
	}

	/**
	 * private static void triggerContentLoad()
	 *     Call JS contentload(), that in turn triggers the homonymous event.
	 *
	 * @return void
	 */
	private static function triggerContentLoad()
	{
		// Call js/contentload() to apply changes to content, menu and navbar
		$page = json_encode(self::$current['page'], JSON_NUMERIC_CHECK);
		$args = json_encode(self::$current['atts'], JSON_NUMERIC_CHECK);

		Response::domready("contentload({$page}, {$args});");
	}

	public static function redirect($page, $atts=[], $msg='', $msgtype='')
	{
		self::setParams($page, $atts);

		$msg && Response::say($msg, $msgtype);

		echo self::html();

		exit(0);
	}

	public static function reload()
	{
		db($_SERVER['REQUEST_ADDR']);
		echo self::html();
	}

	/**
	 * private static void setParams(int $pageid[, array $atts = []])
	 *      Store all info relevant to the current page, as @@current.
	 *
	 * NOTE: $pageid is supposed to be valid and access to the page granted.
	 *
	 * @param int $pageid
	 * @param array $atts
	 */
	private static function setParams($pageid, $atts=[])
	{
		// Store as @@coords for later retrieval, together with atts and tree
		self::$current['page'] = self::pages($pageid);
		self::$current['atts'] = $atts;
		self::$current['tree'] = self::tree($pageid);

		// Handler for this page: {Model}::{action}()
		self::$current['handler'] = self::handler($pageid);
	}

	/**
	 * static mixed getParams([string $key = NULL])
	 *      Return @@current array, or just one of its keys if $key provided.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function getParams($key=NULL)
	{
		$info = self::$current;

		return $key ? (isset($info[$key]) ? $info[$key] : NULL) : $info;
	}

	/**
	 * static bool isHome(int $id)
	 *      Compare given $id with homepage's id.
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function isHome($id)
	{
		return ($id = self::homepage());
	}


	/**
	 * static int homepage()
	 *     Find and validate default page's ID (i.e. homepage).
	 *
	 * @return int
	 */
	public static function homepage()
	{
		static $homepage;

		if (!$homepage)
		{
			// Get default page's id
			if (!defined('HOME') || !HOME)
			{
				$msg = 'No se ha definido una página de inicio (HOME)';
				throw new PublicException($msg);
			}

			$home = join(':', explode(':', HOME) + ['', 'main']);

			foreach (self::pages() as $item)
			{
				$uri = self::uri($item['alias']);
				$fqn = "{$item['model']}:{$item['action']}";

				// Look for home's pageid as well, to fall back if needed
				if (!strcasecmp($home, $fqn) || !strcasecmp(HOME, $uri))
				{
					// Is the homepage's handler valid (i.e. callable)?
					if (!is_callable(self::handler($item['id'])))
					{
						$msg = "La página de inicio definida no es válida" .
						       " ({$item['model']}:{$item['action']})";
						throw new PublicException($msg);
					}

					return ($homepage = $item['id']); # Assignment
				}
			}

			// If we're still here... means we couldn't find it
			$msg = 'No se encontró la página de inicio definida (' . HOME . ')';
			throw new PublicException($msg);
		}

		return $homepage;
	}

	/**
	 * static array pages([int $id = NULL])
	 *      Get a list of all stored pages with their atts. If $id is provided,
	 * return only the page identified by it.
	 *
	 * @param int $id
	 * @return array
	 */
	public static function pages($id=NULL)
	{
		static $pages;

		if (!$pages)
		{
			// Get all pages in the crm_page table
			$sql = "SELECT `m`.*,
			               `p`.*,
			               `m`.`order` AS 'mod_order'
			        FROM `crm_page` `p`
			        LEFT JOIN `crm_module` `m` ON (`p`.`idmodule` = `m`.`id`)
			        ORDER BY `p`.`order`";
			$res = self::s_query($sql)->res;

			while ($row = mysql_fetch_assoc($res))
			{
				// Set default alias if no alias is given
				if (!$row['alias'])
				{
					$row['alias'] = "{$row['model']}:{$row['action']}";
				}

				// Add the page's uri (clean url from alias)
				$row['uri'] = self::uri($row['alias']);

				// Add images (not saved in database)
				$imguri = strtolower("{$row['model']}_{$row['action']}.png");
				$imgpath = PATH_UPLOADS . "/pages/{$imguri}";
				$row['image'] = is_file($imgpath) ? $imguri : NULL;

				$pages[$row['id']] = $row;
			}

			if (!$pages)
			{
				$msg = "No se han encontrado páginas en la base de datos";
				throw new PublicException($msg);
			}
		}

		return $id ? (isset($pages[$id]) ? $pages[$id] : NULL) : $pages;
	}

	/**
	 * static array modules([int $id = NULL])
	 *      Get a list of all stored modules with their atts. If $id is
	 * provided, return only the module identified by it.
	 *
	 * @param int $id
	 * @return array
	 */
	public static function modules($id=NULL)
	{
		static $modules;

		if (!$modules)
		{
			// Get all modules in the crm_module table
			$sql = "SELECT *
			        FROM `crm_module`
			        ORDER BY `order`";
			$res = self::s_query($sql)->res;

			while ($row = mysql_fetch_assoc($res))
			{
				// Add images (not saved in database)
				$imguri = "{$row['id']}.png";
				$imgpath = PATH_UPLOADS . "/modules/{$imguri}";
				$row['image'] = is_file($imgpath) ? $imguri : '__missing__.gif';

				// Shortcut to the uri of this module's main page
				if ($row['mainpage'])
				{
					$row['uri'] = self::pages($row['mainpage'])['uri'];
				}
				else
				{
					$row['uri'] = self::pages(self::homepage())['uri'];
				}

				$modules[$row['id']] = $row;
			}

			if (!$modules)
			{
				$msg = "No se han encontrado módulos en la base de datos";
				throw new PublicException($msg);
			}
		}

		return $id ? (isset($modules[$id]) ? $modules[$id] : NULL) : $modules;
	}

	/**
	 * private static int requestedPage(mixed $input)
	 *      Find out which page the user is trying to load, as described by
	 * $input. It can be a number (pageid), a model, a model:action or an alias.
	 *
	 * Expected behavior:
	 *
	 *    * return NULL if the page does not exist (in table crm_page)
	 *    * throw an exception if $input is ambiguous (more than 1 solution)
	 *    * return the page id of the requested page otherwise
	 *
	 * NOTE: Access rules for requested page are not evaluated in this method.
	 *
	 * @param mixed $input
	 * @return int
	 */
	private static function requestedPage($input)
	{
		$pages = self::pages();

		// Zero is taken as an alias of '', other integers are seen as pageids
		if (is_numeric($input) && (int)$input)
		{
			$pageid = isset($pages[(int)$input]) ? (int)$input : NULL;
		}
		else
		{
			// Fill in defaults if a part of the code is missing
			$input || ($input = HOME);
			$page = join(':', explode(':', $input) + ['', 'main']);

			foreach ($pages as $item)
			{
				// Alias can be module_alias|page_alias, we want only the latter
				$aliases = explode('|', $item['alias']);
				$uri = self::uri(end($aliases));

				$fqn = "{$item['model']}:{$item['action']}";

				// Matches model:action or alias
				if (!strcasecmp($page, $fqn) || !strcasecmp($input, $uri))
				{
					if (isset($pageid))
					{
						$original = $pages[$pageid];
						$page1 = "{$original['model']}:{$original['action']}";
						$page2 = "{$item['model']}:{$item['action']}";

						$msg = "Código de página '{$input}' es ambiguo. Puede" .
						       " hacer referencia a {$page1} o {$page2}";
						throw new PublicException($msg);
					}

					$pageid = $item['id'];
				}
			}
		}

		return isset($pageid) ? $pageid : NULL;
	}

	/**
	 * private static array tree()
	 *      Builds tree of relationship between navigation items (modules, areas
	 * and pages) available from current page.
	 *
	 * @param int $pageid
	 * @return array
	 */
	private static function tree($pageid)
	{
		// Fill defaults
		$tree = ['module'  => [],  # Page's module (defaults to first module)
		         'modules' => [],  # All available modules
		         'pages'   => [],  # Available pages in current module
		         'menu'    => []]; # Menu tree with available pages by area

		// Get allowed modules
		foreach (self::modules() as $module)
		{
			if (Access::canLoad($module['mainpage']))
			{
				$tree['modules'][$module['id']] = $module;
			}
		}

		// Set current module to this page's module. If empty, use first module
		($moduleid = self::pages($pageid)['idmodule'])
		|| ($moduleid = current(array_keys($tree['modules'])));

		$tree['module'] =& $tree['modules'][$moduleid];

		// Get allowed pages in current module
		foreach (self::pages() as $page)
		{
			// Pages without a module (NULL) belong to all modules
			if (!$page['idmodule'] || ($page['idmodule'] == $moduleid))
			{
				if (Access::canLoad($page['id']))
				{
					$tree['pages'][$page['id']] = $page;
				}
			}
		}

		// Build tree structure for menu, with allowed pages in current module
		foreach ($tree['pages'] as $id => &$page)
		{
			// Pages are required to have an area in order to be in the menu
			$page['area'] && ($tree['menu'][$page['area']][] =& $page);
		}

		return $tree;
	}

	private static function hasModule($pageid)
	{
		return !empty(self::pages($pageid)['idmodule']);
	}

	/**
	 * static callback handler(int $pageid)
	 *      Returns the callback ([Object, method]) that's supposed to handle
	 * the current page generation.
	 *
	 * @param int $pageid
	 * @return callback
	 */
	public static function handler($pageid)
	{
		$page = self::pages($pageid);

		if (!$page)
		{
			return NULL;
		}

		return [Model::get(ucfirst($page['model'])), $page['action']];
	}

}