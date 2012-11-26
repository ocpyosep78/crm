<?php

define('SNP_PATH', dirname(__FILE__));

# Internal library structure
define('SNP_BUILDERS', SNP_PATH . '/builders');
define('SNP_LIB', SNP_PATH . '/lib');

# Paths for output (templates, images, styles, jScripts)
define('SNP_TEMPLATES', SNP_PATH . '/templates');
define('SNP_IMAGES', IMAGES . '/snippet');


abstract class Snippet
{

	protected $Model;
	protected $View;

	protected $params;

	private $actions = ['list'    => 'listar %plural%',
	                    'create'  => 'crear %name%',
	                    'view'    => ['title'   => 'ver información de %name%',
	                                  'depends' => '__id__'],
	                    'edit'    => 'editar %name%',
	                    'delete'  => ['title'   => 'eliminar %name%',
	                                  'avoids'  => '__disabled__'],
	                    'restore' => ['title'   => 'restaurar %name%',
	                                  'depends' => '__disabled__']];


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
		$params['basemodel'] = current(explode('.', $model));

		// Starting snippet, regardless of delegates, reads or other chain calls
		empty($params['starter']) && ($params['starter'] = $kind);

		// By default, snippets are to be inserted in the main box (#main_box)
		!empty($params['writeTo']) || ($params['writeTo'] = '#main_box');

		// All snippets must have a groupId, in case they group eachother
		!empty($params['groupId']) || ($params['groupId'] = self::newGroupId());

		// Now fill some more keys just to avoid warnings if polled
		$params += ['id' => '',
		            'buttons' => [],
		            'parent' => NULL,
		            'where' => '',
		            'order' => ''];

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

	private static function newGroupId()
	{
		return str_replace(' ', '', microtime() . rand(0, time()));
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


/******************************************************************************/
/************************ T E M P L A T E   E N G I N E ***********************/
/******************************************************************************/

	final protected function _do()
	{
		$action = $this->params['action'];

		if ($action === 'do')
		{
			$msg = "Action 'do' is reserved and cannot be used as a Snippet action";
			throw new Exception($msg);
		}

		if (is_callable([$this->View, "snp_{$action}"]))
		{
			return $this->View->{"snp_{$action}"}($this->params);
		}
		elseif (is_callable([$this, "_{$action}"]))
		{
			return $this->{"_{$action}"}();
		}
		else
		{
			$msg = "Cannot execute action {$action} on {$this->params['kind']}";
			throw new Exception($msg);
		}
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
	 * final protected XajaxResponse _insert()
	 *      Generates the html of this snippet and inserts it in the page, via
	 * ajax. The target container is set by @params['writeTo'], which defaults
	 * to '#main_box' if ommitted.
	 *
	 * @return XajaxResponse
	 */
	final protected function _insert()
	{
		jQuery($this->params['writeTo'])->html($this->_html());
		return addScript("\$('body').trigger('snippets');");
	}


/******************************************************************************/
/******************************* I N T E R N A L ******************************/
/******************************************************************************/

	/**
	 * protected mixed delegate(string $kind[, array $params])
	 *      Just a handy shortcut to loading other snippets with same model and
	 * same params, save for the $kind and whatever keys come with arr $params.
	 *
	 * @param string $kind
	 * @param array $params
	 * @return mixed
	 */
	protected function delegate($kind, $params=[], $freshstart=false)
	{
		is_array($params) || ($params = $params ? ['action' => $params] : []);

		// Clear groupId when refreshing whole content
		if ($freshstart)
		{
			$params['groupId'] = self::newGroupId();
			$params['starter'] = NULL;
			$params['parent']  = NULL;
		}

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
		$params = ['action' => 'html'];
		$this->params['parent'] || ($params['parent'] = $this->params['kind']);

		return $this->delegate($kind, $params);
	}

	/**
	 * final protected XajaxResponse dialog()
	 *      Pops up $content using jQuery-UI Dialog widget.
	 *
	 * @return XajaxResponse
	 */
	final protected function dialog($content, $elements, $atts=array())
	{
		dialog($content, $elements, $atts);
		return addScript("\$('body').trigger('snippets');");
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

		$this->assign('SNP_TEMPLATES', SNP_TEMPLATES);
		$this->assign('SNP_IMAGES'   , SNP_IMAGES);

		$this->assign('cycleValues', '#eaeaf5,#e0e0e3,#e5e6eb');

		// Internal attributes
		$this->assign('snp_params', $this->params);
		$this->assign('snp_id', $this->params['id']);
		$this->assign('snp_kind', $this->params['kind']);

		// JSON representation of @params
		$this->assign('snp_json_params', toJson($this->params, true));

		// The actual Snippet template (embedded in the common frame)
		$tpl = "/snippets/{$this->params['kind']}.tpl";
		$this->assign('snp_template', SNP_TEMPLATES . $tpl);

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

		$html = $this->fetch(SNP_TEMPLATES . "/global.tpl");

		return $html;
	}


/******************************************************************************/
/******************************** B U T T O N S *******************************/
/******************************************************************************/

	/**
	 * protected array buttons([array $list = NULL[, boolean $view_btns = true]])
	 *      Build a lits of buttons to enable/display for current Snippet.
	 *
	 * @param array $list
	 * @param boolean $view_btns    Whether to include view-specific buttons
	 * @return array
	 */
	protected function buttons($list=NULL, $view_btns=true)
	{
		static $actions;

		if (!$actions)
		{
			$actions = $this->normalizeActions($this->actions, true);
		}

		// Get native buttons
		if (!$list)
		{
			$buttons = is_null($list) ? $actions : [];
		}
		else
		{
			foreach ($list as $axn)
			{
				if (!isset($actions[$axn]))
				{
					$msg = "Button {$axn} was requested but could not be found";
					throw new Exception($msg);
				}

				$buttons[$axn] = $actions[$axn];
			}
		}

		// Get View-specific buttons (if any)
		if ($view_btns)
		{
			$viewAxns = $this->normalizeActions($this->View->actions, false);
			$buttons = array_merge($buttons, $viewAxns);
		}

		return $buttons;
	}

	/**
	 * private array normalizeActions(array &actions)
	 *      Just a handy tool for ::buttons(). Adds missing keys with their
	 * defaults, to get fully described action arrays.
	 *
	 * @param array $actions
	 * @return array
	 */
	private function normalizeActions($actions, $native)
	{
		if (!$actions)
		{
			return [];
		}

		foreach ($actions as $axn => &$props)
		{
			is_array($props) || ($props = ['title' => $props]);

			$props['action'] = $axn;

			$props += ['depends' =>  '', 'avoids' => '', 'native' => $native];

			preg_match('_%([^%]+)%_e', $props['title'], $matches);

			if ($matches)
			{
				if (!property_exists($this->View, "__{$matches[1]}"))
				{
					$msg = "Action name has a wildcard ({$matches[1]}) but " .
						   "there is no View property to replace it with";
					throw new Exception($msg);
				}

				$search = "%{$matches[1]}%";
				$replace = strtolower($this->View->{$matches[1]});
				$subject = $props['title'];

				$props['title'] = str_replace($search, $replace, $subject);
			}

			if (empty($props['icon']))
			{
				if ($native)
				{
					$props['icon'] = SNP_IMAGES . "/buttons/{$axn}.png";
				}
				else
				{
					$static = IMG_PATH . "/{$this->params['basemodel']}";
					$props['icon'] = "{$static}/Snippets/actions/{$axn}.png";
				}
			}

			if (!is_file($props['icon']))
			{
				if (devMode())
				{
					$msg = "Icon for action {$axn} not found at {$props['icon']}";
					throw new Exception($msg);
				}
			}
		}

		return $actions;
	}


/******************************************************************************/
/************************ T E M P L A T E   E N G I N E ***********************/
/******************************************************************************/

	/**
	 * abstract protected void assignVars()
	 *      All Snippets are required to at least declare this method (and most
	 * likely they all need to define some vars for the template to use).
	 *
	 * @return void
	 */
	abstract protected function assignVars();


	final protected function assign($var, $val)
	{
		return $this->View->assign($var, $val, $this->tplNamespace());
	}

	final protected function retrieve($var)
	{
		return $this->View->retrieve($var, $this->tplNamespace());
	}

	final protected function fetch($tpl)
	{
		return $this->View->fetch($tpl, $this->tplNamespace());
	}

	final private function tplNamespace()
	{
		return "snp.{$this->params['groupId']}.{$this->params['kind']}";
	}








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

		return Access::can($what);
	}

	final protected function cant($code)
	{
		return !$this->can($code);
	}

}