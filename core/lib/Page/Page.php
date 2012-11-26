<?php

require_once THIRD_PARTY . '/Smarty/Smarty.class.php';


class Page extends Smarty
{

	use Object;


	public function __construct()
	{
		parent::__construct();

		$this->setConfigDir(SMARTY_DIR . 'configs');
		$this->setTemplateDir(TEMPLATES_PATH);
		$this->setCompileDir(BASE . '/temp');
		$this->setCacheDir(BASE . '/temp');

		// Import all defined constants to the template scope
		$this->assign(get_defined_constants(true)['user']);

		// A snapshot of the current page state (group, area, page, etc)
		$this->assign('pagestate', Access::currentState());

		// Global vars
		$this->assign('USER', getSes('user'));
		$this->assign('USERID', getSes('user'));
		$this->assign('USERNAME', getSes('name').' '.getSes('lastName'));
		$this->assign('PROFILE', getSes('profile'));

		// Debugger
		$this->assign('errMsgs', []);

		// PHP objects we might need
		$this->assign('Xajax', oXajax());
	}

	/**
	 * string page()
	 *      Build and return the HTML for the page, including dynamic content.
	 *
	 * @return string
	 */
	public function page()
	{
		$this->assign('content', $this->content());

		return $this->frame();
	}

	/**
	 * string frame()
	 *      Build the page's HTML, minus #main_box (the actual page content).
	 *
	 * @return string
	 */
	public function frame()
	{
		// Logout button (navbar and menu)
		$this->assign('img_logout', IMG_PATH . '/navButtons/logout.png');

		// Skin chosen from $_GET, constant, or disabled (in that order)
		$skin = !empty($_GET['skin'])
			? $_GET['skin']
			: ((defined('SKIN') && SKIN) ? SKIN : NULL);

		$tpl = $skin ? (CORE_SKINS . "/{$skin}.css") : TEMPLATES . '/main.tpl';
		$css = $skin ? (CORE_SKINS . "/{$skin}.css") : STYLES . '/style.css';

		$this->assign('css', $css);
		$html = $this->fetch($tpl);

		return $html;
	}

	/**
	 * string content()
	 *      Build and return the HTML for #main_box (the actual page content).
	 *
	 * @return string
	 */
	public function content()
	{
		return self::logged() ? '' : $this->fetch(TEMPLATES . '/login.tpl');
	}

	/**
	 * mixed retrieve(string $var)
	 *      Return the value of a previously set template var.
	 *
	 * @param string $var
	 * @return mixed
	 */
	public function retrieve($var)
	{
		return $this->getTemplateVars($var);
	}

}