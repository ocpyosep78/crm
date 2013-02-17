<?php

require_once PATH_THIRDPARTY . '/Smarty/libs/Smarty.class.php';


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

		// Global vars
		$this->assign('NOW', microtime(true));
		$this->assign('USER', getSes('user'));
		$this->assign('USERID', getSes('user'));
		$this->assign('USERNAME', getSes('name').' '.getSes('lastName'));
		$this->assign('PROFILE', getSes('profile'));

		// Debugger
		$this->assign('errMsgs', []);
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