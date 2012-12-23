<?php


class Controller
{

	use Object;


	protected static $request_type;     // What's expected to be returned?

	protected static $request;
	protected static $params;


	public static function process()
	{
		self::$request_type = 'print';

		// Translate params
		isset($_GET['params']) || ($_GET['params'] = 'home');
		$params = explode('/', trim($_GET['params'], '/'));

		array_map('urldecode', $params);

		self::$request = Sugar::page(array_shift($params), true);
		self::$params = $params;

		if (self::ajax())
		{
			require_once dirname(__FILE__) . '/Controller.Ajax.php';
			$Controller = new Controller_Ajax;
		}
		else
		{
			require_once dirname(__FILE__) . '/Controller.Load.php';
			$Controller = new Controller_Load;
		}

		$Controller->req();
	}

	protected function req()
	{
		FileForm::processRequests();

		switch (self::$request_type)
		{
			case 'print':
				echo $this->content();
				exit(0);

			default:
				throw new InvalidRequestException($type);
		}
	}

	/**
	 * protected string content()
	 *      Build and return the HTML for #main_box (the actual page content).
	 *
	 * @return string
	 */
	protected function content()
	{
		// No content is to be shown for guests, other than the login screen
		if (!self::logged())
		{
			return Template::one()->fetch('login.tpl');
		}

		$page = self::$request;
		$fn = "page_{$page}";

		switch (count(self::$params))
		{
			case 0: $fn();
				break;
			case 1: $fn(self::$params[0]);
				break;
			case 2: $fn(self::$params[0], self::$params[1]);
				break;
			case 3: $fn(self::$params[0], self::$params[1], self::$params[2]);
				break;
			default: call_user_func_array($fn, self::$params[1]);
				break;
		}

		$tpl = Access::pageArea($page)."/{$page}.tpl";
		$path = TEMPLATES_PATH . "/{$tpl}";

		if (!is_file($path))
		{
			$msg = "No se pudo encontrar la plantilla de la página ({$tpl})";
			throw new Exception($msg);
		}
Template::one()->fetch($path);
db('ok');
		return Template::one()->fetch($path);
	}

	protected function login()
	{
		return Template::one()->fetch('login.tpl');
	}

	public static function js($js)
	{
		if (!Template::one())
		{
			$msg = "Cannot call Controller::js before calling Controller::process";
			throw new Exception($msg);
		}

		return Template::one()->append('js', $js);
	}

}