<?php

require_once THIRD_PARTY . '/Smarty/Smarty.class.php';


class Template extends Smarty
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