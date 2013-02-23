<?php


class Controller
{

	use Object;


	public static function process()
	{
		if (self::ajax())
		{
			// jQuery won't send empty arrays through ajax
			$args = self::readAjaxArgs();

			// For guests, block all ajax calls except 'login'
			if (!self::logged() && !self::ajax('login'))
			{
				Response::reload();
			}
			elseif(self::ajax('content'))
			{
				$page = array_shift($args)['page'];
				$atts = array_shift($args);

				Response::content(self::page($page, $atts, true), true);
			}
			else
			{
				call_user_func_array(['Ajax', self::ajax()], $args);
			}

			$response = Template::one()->retrieve('js');

			if (!$response)
			{
				$response = ["say('Error: no response returned by server')"];
			}

			echo json_encode($response);
		}
		else
		{
			// Translate args
			!empty($_GET['args']) || ($_GET['args'] = 'home');
			$args = explode('/', trim($_GET['args'], '/'));

			$page = Sugar::page(array_shift($args), true);
			echo self::page($page, $args);

			exit(0);
		}
	}

	/**
	 * private string page(string $page, array $args)
	 *      Build and return the HTML for the page.
	 *
	 * @param string $page
	 * @param array $args
	 * @return string
	 */
	private static function page($page, $args, $onlyContent=false)
	{
		// Import a few constants into the javascript global scope
		Response::importConst('DEVMODE', 'BBURL', 'IMAGES_URL');

		// Content
		if (!self::logged())
		{
			// No content is to be shown for guests, other than the login screen
			$content = Template::one()->fetch(PATH_TPLS . '/login.tpl');
		}
		else
		{
			// Show menu for all pages unless explicitely told otherwise
			Response::showMenu();

			$fn = "page_{$page}";

			// Default to page 'home' if page is empty or invalid
			if (!$page || !is_callable($fn))
			{
				$fn = 'page_' . Sugar::page(Sugar::home(), true);
				$args = [];
			}

			switch (count($args))
			{
				case 0: $fn();
					break;
				case 1: $fn($args[0]);
					break;
				case 2: $fn($args[0], $args[1]);
					break;
				case 3: $fn($args[0], $args[1], $args[2]);
					break;
				default: call_user_func_array($fn, $args);
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

		// Only content or the whole page?
		if ($onlyContent)
		{
			// Simple wrapper, mainly for javascript plus the content's html
			$html = Template::one()->fetch(PATH_TPLS . '/content.tpl');
		}
		else
		{
			Response::say();

			// A snapshot of the current page state (group, area, page, etc)
			Template::one()->assign('pagestate', Access::environment($page));

			// Logout button (navbar and menu)
			$img_logout = IMAGES_URL . '/navButtons/logout.png';
			Template::one()->assign(compact('img_logout'));

			// Skin chosen from $_GET, constant, or disabled (in that order)
			$skin = !empty($_GET['skin'])
				? $_GET['skin']
				: ((defined('SKIN') && SKIN) ? SKIN : NULL);

			$tpl = $skin ? (URL_SKINS . "/{$skin}.css") : PATH_TPLS . '/main.tpl';
			$css = $skin ? (URL_SKINS . "/{$skin}.css") : URL_STYLES . '/style.css';

			Template::one()->assign('css', $css);
			$html = Template::one()->fetch($tpl);
		}

		return $html;
	}

	private static function readAjaxArgs($args=NULL)
	{
		if (is_null($args))
		{
			$args = empty($_POST['args']) ? [] : json_decode($_POST['args']);
		}

		if (!is_scalar($args))
		{
			foreach ((array)$args as $k => $arg)
			{
				$newargs[$k] = self::readAjaxArgs($arg);
			}

			$args = $newargs;
		}

		return $args;
	}

}