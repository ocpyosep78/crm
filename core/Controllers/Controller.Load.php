<?php


class Controller_Load extends Controller
{

	/**
	 * protected string content()
	 *      Overrides parent's content() to wrap the content's HTML within the
	 * StaticFrame (menu, navbar, widgets, debugTools, etc).
	 *
	 * @return string
	 */
	protected function content()
	{
		header("Content-Type: text/html; charset=iso-8859-1");

		Template::one()->assign('content', parent::content());
db('oi');
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
		Template::one()->assign('img_logout', IMG_PATH . '/navButtons/logout.png');

		// Skin chosen from $_GET, constant, or disabled (in that order)
		$skin = !empty($_GET['skin'])
			? $_GET['skin']
			: ((defined('SKIN') && SKIN) ? SKIN : NULL);

		$tpl = $skin ? (CORE_SKINS . "/{$skin}.css") : TEMPLATES . '/main.tpl';
		$css = $skin ? (CORE_SKINS . "/{$skin}.css") : STYLES . '/style.css';

		Template::one()->assign('css', $css);
		$html = Template::one()->fetch($tpl);

		return $html;
	}

}