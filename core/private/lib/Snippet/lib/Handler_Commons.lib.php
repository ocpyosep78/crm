<?php

/**
 * DEPENDS ON:
 *
 *    class snp_Layers
 *        layer ajax
 *            #display( string $msg , string $errType )
 *        layer error
 *            #Error( string $msg )
 *    class Snippet_Handler_Source
 *        #inject( string $snippet , string $code , array $params )
 *        #aquire()
 *        #validate()
 *        #
 *        #
 *    constant SNIPPET_DEFINITION_PATH
 */

/**
 * abstract class Snippets_Handler_Commons
 *
 * @overview: This class ties together a particular Handler class (descending
 *            from this one) with the information provided by a definition file
 *            (created by users, following conventions).
 *
 *            It gets assistance from its sibling Snippets_Handler_Source
 *            for parsing and interpretting the definitions, thus decompressing
 *            the logic it has to process for each snippet request. Commons
 *            stays at a higher level and receives digested data, not minding
 *            how or where it came from.
 *
 *            Definition files extend Snippets_Handler_Source, so we get
 *            all of its methods by loading the definition class script and
 *            instantiating it. Within the class, it is stored as @Source.
 *
 *            This class attempts to bring results even if everything's broken,
 *            falling back to defaults or empty sets of data, to avoid fatal
 *            errors for bad configuration. It does, however, cause warnings
 *            in the shape of messages for the user, by calling the ajax layer
 *            method #display(string $msg, mixed $type).
 */


abstract class Snippets_Handler_Commons{

	protected $snippet;

	protected $code;
	protected $params;

	protected $Layers;
	protected $Source;
	protected $Access;

	private $tplVars;

	protected $basics;
	protected $fields;
	protected $key;
	protected $tools;


	public function __construct( $snippet ){

		$this->Layers = new snp_Layers;

		$this->Access = $this->Layers->get('access');

		$this->snippet = $snippet;

		$this->tplVars = array();

	}

	public function start($code, $params)
	{
		$this->code = $code;
		$this->params = $params;

		# Set @Source for the current $code (module's code name)
		$this->buildSource();

		# Get a general idea of the integrity of the definitions
		$integrity = $this->Source->validate();

		return $this;
	}

	public function getSnippet()
	{
		# Register common/config data (fields, key, tools)
		$this->registerCommonVars()->registerConfig();

		# Pass control to the specific handler
		# (child that inherited from this one)
		return is_callable(array($this, "handle_{$this->snippet}"))
			? $this->{"handle_{$this->snippet}"}()
			: Snippet_Tools::issueWarning("handle_{$this->snippet} not found");
	}

	protected function showToolTip($field, $msg)
	{
		$uID = $this->params['groupId'];
		$snippet = "{$this->snippet}Item";

		$cmd = "Snippets.get('{$uID}', '{$snippet}').tooltip('{$field}', '{$msg}');";

		$this->Layers->get('ajax')->addScript( $cmd );

		return $this;

	}

	protected function hideToolTip(){

		$uID = $this->params['groupId'];
		$snippet = "{$this->snippet}Item";

		$cmd = "Snippets.get('{$uID}', '{$snippet}').tooltip();";

		$this->Layers->get('ajax')->addScript( $cmd );

		return $this;

	}

	protected function validateInput( $data )
	{
		$this->hideToolTip();

		$res = $this->Source->validateInput( $data );

		# Show tool tips where enabled if validation failed
		if( $res !== true ){
			$this->Layers->get('ajax')->display('Falló la validación de los datos ingresados');
			$this->showToolTip($res['field'], $res['tip']);
		}

		return $res === true;
	}


##################################
########### GET SOURCE ###########
##################################

	private function buildSource()
	{
		$Modules = $this->Layers->get('modules');
		$this->Source = $Modules->getModule($this->code, $this->params);
	}


##################################
############ TEMPLATE ############
##################################

	private function registerCommonVars()
	{
		# General and presentational
		$this->assign('cycleValues', '#eaeaf5,#e0e0e3,#e5e6eb');
		$this->assign('SNIPPET_TEMPLATES', SNIPPET_TEMPLATES);
		$this->assign('SNIPPET_IMAGES', SNIPPET_IMAGES);
		$this->assign('DEVELOPER_MODE', defined('DEVELOPER_MODE') ? DEVELOPER_MODE : false);

		# Internal attributes
		$this->assign('snippet', $this->snippet);
		$this->assign('code', $this->code);
		$this->assign('groupId', $this->params['groupId']);
		$this->assign('params', Snippet_Tools::toJson($this->params));

		# Common attributes
		$this->basics = $this->Source->getBasics();
		$this->assign('name', $this->basics['name']);
		$this->assign('plural', $this->basics['plural']);
		$this->assign('gender', $this->basics['gender']);
		$this->assign('tipField', 'toolTipText');

		return $this;
	}

	private function registerConfig()
	{
		########## FIELDS ###########
		$dt = $this->getDataType();

		$fields = $dt ? $this->Source->getFields($dt) : array();
		$this->fields = $this->assign('fields', $fields);

		########## KEYS ###########
		$this->key = $this->assign('key', $this->Source->getSummary('key'));

		########## TOOLS ###########
		$this->tools = $this->assign('tools', $this->Source->getSummary('tools'));

		return $this;

	}

	private function getDataType()
	{
		switch ($this->snippet)
		{
			case 'commonList':
			case 'innerCommonList':
			case 'simpleList':
			case 'comboList':
				return 'list';

			case 'viewItem':
			case 'editItem':
			case 'createItem':
				return preg_replace('/Item$/', '', $this->snippet);

			default:
				return NULL;
		}
	}

	/**
	 * Returns an HTML string from a template, after assigning all vars
	 * passed as $data (retains previously assigned vars).
	 */
	protected function fetch($name, $data=array()){

		# Register all stored vars in the Template Engine
		foreach( $data + $this->tplVars as $k => $v ){
			$this->Layers->get('templates')->assign($k, $v);
		}

		$name = preg_replace('/\.tpl$/', '', $name);
		if( !is_file(SNIPPET_TEMPLATES."/{$name}.tpl") ) $name = '404';

		$pathToFile = SNIPPET_TEMPLATES."/{$name}.tpl";
		$this->Layers->get('templates')->assign('pathToTemplate', $pathToFile);

		return $this->Layers->get('templates')->fetch('global.tpl');

	}

	protected function assign($var, $val=NULL){

		return $this->tplVars[$var] = $val;

	}

	protected function clearVar( $var=NULL ){

		if( is_null($var) ) $this->tplVars = array();
		else unset( $this->tplVars[$var] );

	}

	protected function disableBtns( $tools ){

		foreach( (array)$tools as $tool ){
			$this->tplVars['tools'][$tool]['disabled'] = true;
		}

	}

}