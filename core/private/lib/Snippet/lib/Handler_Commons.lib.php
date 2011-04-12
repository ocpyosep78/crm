<?php

/**
 * DEPENDS ON:
 * 
 *    class Snippet_Layers
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
 * abstract class Snippets_Handlers_Commons
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
	

	abstract class Snippets_Handlers_Commons{
	
		protected $dataType;		# list, item
	
		protected $snippet;
		
		protected $code;
		protected $params;
		
		protected $Layers;
		protected $Source;
		protected $Access;
		
		private $tplVars;
		
		protected $basics;
		protected $fields;
		protected $keys;
		protected $tools;
		
	
		public function __construct( $snippet ){
		
			$this->Layers = new Snippet_Layers;
			
			$this->Access = $this->Layers->get('access');
			
			$this->snippet = $snippet;
			
			$this->tplVars = array();
			
		}
		
		public function start($code, $params){
			
			$this->code = $code;
			$this->params = $params;
			
			# Set @Source for the current $code (module's code name)
			$this->defineSource();
			
			# Initiate required properties of the object
			$this->Source->inject($this->snippet, $code, $params);
			
			# Have it aquire data and fill gaps in the definition file
			$this->Source->aquire();
			
			# Get a general idea of the integrity of the definitions
			$integrity = $this->Source->validate();
			
			return $this;
			
		}
	
		public function getSnippet(){
		
			# Register common/config data (fields, keys, tools)
			$this->registerCommonVars()->registerConfig();
			
			# Pass control to the specific handler
			# (child that inherited from this one)
			return is_callable(array($this, "handle_{$this->snippet}"))
				? $this->{"handle_{$this->snippet}"}()
				: Snippet_Tools::issueWarning("handle_{$this->snippet} not found");
		
		}
		
		protected function toolTip($field, $msg){
		
			$Ajax = $this->Layers->get('ajax');
			$uID = $this->params['group_uID'];
			
			$Ajax->addScript("Snippet.showToolTip('{$uID}', '{$field}', '{$msg}');");
			
		}
		
		protected function hideToolTip(){
		
			$Ajax = $this->Layers->get('ajax');
			$uID = $this->params['group_uID'];
		
			$Ajax->addScript("Snippet.hideToolTip('{$uID}');");
			
		}
		
		protected function validateInput( $data ){
		
			$Valid = new Snippet_Validation;
			
			$res = $Valid->test($data, $this->Source->getRuleSet());
			
			# Show tool tips where enabled if validation failed
			if( $res !== true ){
				$this->Layers->get('ajax')->display('Falló la validación de los datos ingresados');
				$this->toolTip($res['field'], $res['tip']);
			}
			else $this->hideToolTip();
			
			return $res === true;
		
		}


##################################
########### GET SOURCE ###########
##################################
		
		private function defineSource(){
			
			$file = SNIPPET_DEFINITION_PATH."/{$this->code}.def.php";
			$class = "Snippet_def_{$this->code}";
			
			# Attempt to load the definition file
			if( is_file($file) ) require_once( $file );
			if( class_exists($class) ){
				$this->Source = new $class;
			}
			# If not found, use the naked Source as $Source
			else{
				$this->registerWarning('definition class missing');
				$this->Source = new Snippets_Handler_Source;
			}
			
		}


##################################
############ TEMPLATE ############
##################################
		
		private function registerCommonVars(){
		
			# General and presentational
			$this->assign('cycleValues', '#eaeaf5,#e0e0e3,#e5e6eb');
			$this->assign('SNIPPET_TEMPLATES', SNIPPET_TEMPLATES);
			$this->assign('SNIPPET_IMAGES', SNIPPET_IMAGES);
			$this->assign('DEVELOPER_MODE', defined('DEVELOPER_MODE') ? DEVELOPER_MODE : false);
			
			# Internal attributes
			$this->assign('snippet', $this->snippet);
			$this->assign('code', $this->code);
			$this->assign('params', Snippet_Tools::toJson($this->params));
			
			# Common attributes
			$this->basics = $this->Source->getBasics();
			$this->assign('name', $this->basics['name']);
			$this->assign('plural', $this->basics['plural']);
			$this->assign('gender', $this->basics['gender']);
			$this->assign('tipField', 'toolTipText');
			
			return $this;
			
		}
		
		private function registerConfig(){
			
			########## FIELDS ###########
			
			# If dataType (item, list) wasn't explicitly set, try to guess it
			if( !$this->dataType ){
				$found = preg_match('/Snippet_hnd_(.+)/', get_class($this), $class);
				if( $found ) $this->dataType = $class[1];
			}
			
			# Get fields for this dataType (with attributes)
			$fields = $this->Source->getFieldsWithAtts( $this->dataType );
			$this->fields = $this->assign('fields', $fields);
			
			########## KEYS ###########
			
			$this->keys = $this->assign('keys', $this->Source->getSummary('keys'));
			
			########## TOOLS ###########
			
			$this->tools = $this->assign('tools', $this->Source->getSummary('tools'));
			
			return $this;
		
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

?>