<?php

define('SNP_PATH', dirname(__FILE__));

# Internal library structure
define('SNP_BUILDERS', SNP_PATH . '/builders');
define('SNP_LIB', SNP_PATH . '/lib');

# Paths for output (templates, images, styles, jScripts)
define('SNP_TEMPLATES', SNP_PATH . '/templates');
define('SNP_IMAGES', CORE_IMAGES . 'snippet');


# Include common internal classes
//require_once SNP_LIB . '/Initialize.lib.php';
//require_once SNP_LIB . '/Tools.lib.php';
//require_once SNP_LIB . '/Layers.lib.php';
//require_once SNP_LIB . '/Validation.lib.php';



class SNP
{

	protected $params;

	protected $Model;
	protected $View;

	protected $html;

	protected $tools = array(
		'list'		=> 'listado',
		'create'	=> 'agregar',
		'view'		=> 'ver información de',
		'edit'		=> 'editar',
		'block'		=> 'bloquear',
		'unblock'	=> 'desbloquear',
		'delete'	=> 'eliminar',
	);


	/**
	 * string getSnippet(string $snippet, string $code[, array $params = array()])
	 *		Creates an Initialize object and calls its getSnippet method,
	 * forwarding what it returns as its own return value (HTML str expected).
	 *
	 * @returns: Snippet subclass
	 */
	public static function getSnippet($snippet, $model, $params=array())
	{
		is_array($params) || $params = array('modifier' => $params);

		// Having $snipet and $model appart was just to show they were required
		$params['snippet'] = $snippet;
		$params['model'] = $model;
		!empty($params['writeTo']) || ($params['writeTo'] = 'main_box');

		$path = SNP_BUILDERS . '/' . ucfirst($snippet) . '.php';
		$class = 'snp_' . ucfirst($snippet);

		is_file($path) && include_once $path;

		if (!class_exists($class))
		{
			$msg = "Unable to find Snippet Builder class {$class}";
			throw new Exception($msg);
		}

		$Snippet = (new $class($params));

		return $Snippet->html()->lateTasks();
	}


	/**
	 * void __construct(array $params)
	 *      Initialize Snippet sub-object.
	 *
	 * @param array $params
	 */
	public function __construct($params)
	{
		$this->params = $params;

		$this->Model = Model::get($params['model']);
		$this->View = View::get($params['model']);
	}

	protected function html()
	{
		$this->assignCommonVars()->assignSnippetVars();

		$snippet = $this->params['snippet'];
		$this->html = $this->View->fetch(SNP_TEMPLATES . "/global.tpl");

		return $this;
	}

	protected function lateTasks()
	{
		// Print the HTML inside the given element (by default, #main_box).
		addAssign($this->params['writeTo'], 'innerHTML', $this->html);

		return addScript("\$('body').trigger('snippets');");
	}

	/**
	 * private SNP registerCommonVars()
	 *      Register most common vars for the view to use
	 *
	 * @return SNP
	 */
	private function assignCommonVars()
	{
		$this->View->assign('SNP_TEMPLATES', SNP_TEMPLATES);
		$this->View->assign('SNP_IMAGES'   , SNP_IMAGES);

		$this->View->assign('cycleValues', '#eaeaf5,#e0e0e3,#e5e6eb');
		$this->View->assign('DEVMODE', devMode());

		// Internal attributes
		$this->View->assign('params', $this->params);
		$this->View->assign('snippet', $this->params['snippet']);
		$this->View->assign('json_params', toJson($this->params, true));

		// The actual Snippet template (embedded in the common frame)
		$tpl = "/snippets/{$this->params['snippet']}.tpl";
		$this->View->assign('snp_template', SNP_TEMPLATES . $tpl);

		return $this;
	}

	protected function getTools()
	{
		$tools = array();

		foreach (func_get_args() as $tool)
		{
			$tools[$tool]['name'] = $this->tools[$tool];
			$tools[$tool]['disabled'] = !isset($this->tools[$tool]);
		}

		return $tools;
	}

	/**
	 * void SNP assignSnippetVars()
	 *      Vars that might be required by the view, besides the common ones.
	 *
	 * @return SNP
	 */
	protected function assignSnippetVars() { return $this; }








	public function can($code)
	{
		$snippet = $this->params['snippet'];

		switch ($snippet)
		{
			case 'list':
			case 'commonList':
			case 'innerCommonList':
			case 'simpleList':
				$what = $code;
				break;

			case 'createItem':
			case 'editItem':
			case 'deleteItem':
				$what = preg_replace('/Item$/i', '', $snippet) . ucfirst($code);
				break;

			case 'create':
			case 'edit':
			case 'delete':
			case 'block':
			case 'unblock':
				$what = $snippet . ucfirst($code);
				break;

			case 'view':
			case 'viewItem':
				$what = "{$code}Info";
				break;

			default:
				return false;
		}

		return oPermits()->can($what);
	}

	public function cant($code)
	{
		return !$this->can($code);
	}

}