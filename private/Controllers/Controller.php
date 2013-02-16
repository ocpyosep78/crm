<?php


class Controller
{

	use Object;


	protected static $request_type;     // What's expected to be returned?

	protected static $request;
	protected static $params;


	public static function process()
	{
		self::$request_type = 'page';

		// Translate params
		isset($_GET['params']) || ($_GET['params'] = 'home');
		$params = explode('/', trim($_GET['params'], '/'));

		self::$request = Sugar::page(array_shift($params), true);
		self::$params = $params;

		switch (self::$request_type)
		{
			case 'page':
				echo self::page();
				exit(0);

			default:
				throw new InvalidRequestException($type);
		}
	}

	/**
	 * private string page()
	 *      Build and return the HTML for #main_box (the actual page content).
	 *
	 * @return string
	 */
	private static function page()
	{
		header("Content-Type: text/html; charset=utf8");

		// Import a few constants into the javascript global scope
		Response::importConst('DEVMODE', 'BBURL');

		// Content
		if (!self::logged())
		{
			// No content is to be shown for guests, other than the login screen
			$content = Template::one()->fetch('login.tpl');
		}
		else
		{
			// Show menu for all pages unless explicitely told otherwise
			Response::showMenu();

			$page = self::$request;
			$fn = "page_{$page}";
			$params = self::$params;

			if (!is_callable($fn))
			{
				$fn = 'page_' . Sugar::page(Sugar::home(), true);
				$params = [];
			}

			switch (count($params))
			{
				case 0: $fn();
					break;
				case 1: $fn($params[0]);
					break;
				case 2: $fn($params[0], $params[1]);
					break;
				case 3: $fn($params[0], $params[1], $params[2]);
					break;
				default: call_user_func_array($fn, $params);
					break;
			}

			$tpl = Access::pageArea($page)."/{$page}.tpl";
			$path = realpath(TEMPLATES_PATH . "/{$tpl}");

			if (!is_file($path))
			{
				$content = "No se ha encontrado la plantilla ({$tpl})";
			}
			else
			{
				Response::js("iniPage('{$page}')");
				$content = Template::one()->fetch($path);
			}
		}

		Template::one()->assign('content', $content);

		// Frame
		if (self::ajax())
		{
			// Simple wrapper, mainly for javascript plus the content's html
			$html = Template::one()->fetch(TEMPLATES . '/content.tpl');
		}
		else
		{
			// Logout button (navbar and menu)
			Template::one()->assign('img_logout', IMG_PATH . '/navButtons/logout.png');

			// Skin chosen from $_GET, constant, or disabled (in that order)
			$skin = !empty($_GET['skin'])
				? $_GET['skin']
				: ((defined('SKIN') && SKIN) ? SKIN : NULL);

			$tpl = $skin ? (CORE_SKINS . "/{$skin}.css") : TEMPLATES . '/main.tpl';
			$css = $skin ? (CORE_SKINS . "/{$skin}.css") : STYLES_URL . '/style.css';

			Template::one()->assign('css', $css);
			$html = Template::one()->fetch($tpl);
		}

		return $html;
	}

}