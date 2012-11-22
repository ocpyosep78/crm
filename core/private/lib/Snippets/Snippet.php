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



abstract class SNP
{

	protected $params;

	protected $Model;
	protected $View;


	/**
	 * string getSnippet(string $kind, string $code[, array $params = array()])
	 *		Creates an Initialize object and calls its getSnippet method,
	 * forwarding what it returns as its own return value (HTML str expected).
	 *
	 * @returns: Snippet subclass
	 */
	public static function snp($kind, $model, $params=[])
	{
		// If $params is a string, interpret it as the action to execute
		is_array($params) || ($params = $params ? ['action' => $params] : []);

		// The action is by default is ::insert(), but it can be overridden
		!empty($params['action']) || ($params['action'] = 'insert');

		// Having $snipet and $model appart was just to show they were required
		$params['kind'] = $kind;
		$params['model'] = $model;

		// By default, snippets are to be inserted in the main box (#main_box)
		!empty($params['writeTo']) || ($params['writeTo'] = '#main_box');

		// All snippets must have a unique groupId, in case they group eachother
		$groupId = str_replace(' ', '', microtime() . rand(0, time()));
		!empty($params['groupId']) || ($params['groupId'] = $groupId);

		// Now fill some more keys just to avoid warnings if polled
		$params += ['id' => '', 'where' => '', 'order' => ''];

		// Call the handler, for non-common tasks of this particular Snippet
		$path = SNP_BUILDERS . '/' . ucfirst($kind) . '.php';
		$class = 'snp_' . ucfirst($kind);

		is_file($path) && include_once $path;

		if (!class_exists($class))
		{
			$msg = "Unable to find Snippet Builder class {$class}";
			throw new Exception($msg);
		}

		return (new $class($params))->_do();
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

		$this->Model = Model::get($params['model'], 'snp');
		$this->View  = View::get($params['model'], 'snp');
	}

	/**
	 * protected mixed delegate(string $kind[, array $params])
	 *      Just a handy shortcut to loading other snippets with same model and
	 * same params, save for the $kind and whatever keys come with arr $params.
	 *
	 * @param string $kind
	 * @param array $params
	 * @return mixed
	 */
	protected function delegate($kind, $params=[])
	{
		is_array($params) || ($params = $params ? ['action' => $params] : []);
		return self::snp($kind, $this->params['model'], $params + $this->params);
	}

	/**
	 * protected string read(string $kind)
	 *      Get the generated HTML of another snippet (usually to be integrated
	 * within this one). The requested snippet will receive a param 'parent',
	 * indicating who's carrying the request.
	 *
	 * @param string $kind
	 * @return mixed
	 */
	protected function read($kind)
	{
		$params = ['parent' => $this->params['kind'], 'action' => 'html'];
		return self::delegate($kind, $params);
	}

	final protected function _do()
	{
		$action = "_{$this->params['action']}";

		if (($action !== 'do') && !is_callable([$this, $action]))
		{
			$action = $this->params['action'];
			$kind = $this->params['kind'];

			$msg = "Attempt to call action {$action} on {$kind} failed";
			throw new Exception($msg);
		}

		return $this->$action();
	}

	/**
	 * final protected XajaxResponse _insert()
	 *      Generates the html of this snippet and inserts it in the page, via
	 * ajax. The target container is set by $params['writeTo'], which defaults
	 * to '#main_box' if ommitted.
	 *
	 * @return XajaxResponse
	 */
	final protected function _insert()
	{
		// Print the HTML inside the given element (by default, #main_box).
		jQuery($this->params['writeTo'])->html($this->_html());

		return addScript("\$('body').trigger('snippets');");
	}

	/**
	 * protected string _html()
	 *      Just a valid method name for an action that just returns the html().
	 *
	 * @return string
	 */
	protected function _html()
	{
		return $this->html();
	}

	/**
	 * protected string html()
	 *      Generate the output of this snippet.
	 *
	 * @return string
	 */
	final protected function html()
	{
		$this->assignVars();

		$this->View->assign('SNP_TEMPLATES', SNP_TEMPLATES);
		$this->View->assign('SNP_IMAGES'   , SNP_IMAGES);

		$this->View->assign('cycleValues', '#eaeaf5,#e0e0e3,#e5e6eb');

		// Internal attributes
		$this->View->assign('snp_params', $this->params);
		$this->View->assign('snp_id', $this->params['id']);
		$this->View->assign('snp_kind', $this->params['kind']);
		$this->View->assign('snp_json_params', toJson($this->params, true));

		// The actual Snippet template (embedded in the common frame)
		$tpl = "/snippets/{$this->params['kind']}.tpl";
		$this->View->assign('snp_template', SNP_TEMPLATES . $tpl);

		// What if the Snippet doesn't have a template yet?
		if (!is_file(SNP_TEMPLATES . $tpl))
		{
			// No need to abort if this is just a part of a larger Snippet
			if (!empty($this->params['parent']) && !devMode())
			{
				return '';
			}

			$kind = ucfirst($this->params['kind']);
			$msg = "Snippet {$kind} is missing its template";
			throw new Exception($msg);
		}

		$html = $this->View->fetch(SNP_TEMPLATES . "/global.tpl");

		return $html;
	}

	/**
	 * abstract protected void assignVars()
	 *      All Snippets are required to at least declare this method (and most
	 * likely they all need to define some vars for the template to use).
	 *
	 * @return void
	 */
	abstract protected function assignVars();








	final protected function can($code)
	{
		$kind = $this->params['kind'];

		switch ($kind)
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
				$what = preg_replace('/Item$/i', '', $kind) . ucfirst($code);
				break;

			case 'create':
			case 'edit':
			case 'delete':
			case 'block':
			case 'unblock':
				$what = $kind . ucfirst($code);
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

	final protected function cant($code)
	{
		return !$this->can($code);
	}

}