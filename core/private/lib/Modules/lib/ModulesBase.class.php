<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */
	
	
	/**
	 * A module represents a kind of object that's itself a list of objects. To
	 * avoid missunderstandings, here we'll refer to the objects represented by
	 * this class as modules, and as items its individual objects. As an example,
	 * module Users represents all the users of the application, and each user is
	 * an item of that module.
	 * 
	 * Most modules share methods like 'list your items' or 'add an item', while
	 * their items often have methods 'create', 'view', 'edit', 'block', 'delete'.
	 * 
	 * The goal of this class (and its children) is to grab what's common to all
	 * (or most) of the modules, ask external files about what's special or
	 * particular in a given Module, using a concise and compact protocol, and put
	 * it all together to answer for the abstract Module when the application
	 * requests something from it.
	 * 
	 * There is a limited specialization of classes, where Module_Lists handles
	 * mostly lists, Module_Info handles info page, and Module_Edit handles both
	 * create and edit pages. However, to avoid duplicating code, considering lists
	 * might be partially included in info pages (in particular comboList is most
	 * likely to be included in all info, edit and create pages), and info might be
	 * included in lists (when hovering an item, for example), most common methods
	 * are in this class instead.
	 * 
	 * There is a very important reason though, to organize it all this way. Having
	 * different classes that descend from this one, the same methods can be reused,
	 * which is specially important for the main methods (only ones seen from the
	 * outside): #getPage and #doTasks.
	 */


	abstract class ModulesBase extends Connection{
		

##################################
########### PROPERTIES ###########
##################################
		
		# Available tools with screen name
		private $toolsBase = array(
			'open'		=> 'abrir',
			'create'	=> 'agregar',
			'edit'		=> 'editar',
			'block'		=> 'bloquear',
			'delete'	=> 'borrar',
		);
		
		# Available field types
		private $fieldTypes = array('text', 'image', 'area', 'combo');
		
		
		protected $recordedError;
	
		protected $type;			/* Type of page, for page-handlers with multiple types (i.e. Module_Lists) */
		protected $code;			/* Unique identifier for this module */
		protected $modifier;		/* Some modules might have different behaviours depending on a parameter */
		protected $params;			/* Wildcard var to pass extra parameters (each Handler knows its own) */
		
		protected $fields;			/* List of fields for current page */
		protected $keys;			/* Key fields for current module */
		protected $tools;			/* Actions enabled for this module (create, edit, etc.) */
		
		protected $dataCache;		/* Cached lists data in case we need to reuse it */
		
		protected $DP;				/* Data Provider for this module (user-configured) */
		protected $dataProvider;	/* Whether the data provider could be found (boolean) */
		
		protected $AjaxEngine;
		protected $TemplateEngine;
		private $vars;				/* Registers vars to be used in the templates */
		

##################################
########## CONSTRUCTOR ###########
##################################

		public function __construct($type, $code, $modifier, $params){
		
			parent::__construct();
		
			# Main parameters for this module
			$this->type = $type;
			$this->code = $code;
			$this->modifier = $modifier;
			$this->params = $params;
			
			# Initialize only
			$this->keys = array();
			$this->fields = array();
			$this->tools = array();
			
			# Template engine
			$this->AjaxEngine = new Modules_ajaxEngine;
			$this->TemplateEngine = new Modules_templateEngine;
			$this->vars = array();
			
			# Keep track of errors between methods
			$this->recordedError = NULL;
		
		}


##################################
############# PUBLIC #############
##################################
		
		/**
		 * @overview: This is the class' main method. Module Handlers (descended from
		 *            this one) have here their initialization and validation of input.
		 *            It is ModulesBase, through its few public methods, that calls
		 *            methods in Handlers (they're all protected or private).
		 *            It is important to notice that Handlers have no knowledge of pages
		 *            or how the returned HTML string is going to be presented. Handlers
		 *            only know about atomic elements, that ModulesCreator might combine
		 *            (or not) in order to make pages.
		 * @returns: an HTML string
		 */
		public function getElement(){
		
			# Set main local vars, get Data Provider
			$this->initialize();
			if( $this->recordedError ) return $this->Error( $this->recordedError );
			
			# Let the right method handle the rest, depending on $this->type
			$HTML = $this->{$this->type}();
			
			return $this->recordedError ? $this->Error($this->recordedError) : $HTML;
			
		}
		
		/**
		 * 
		 */
		public function doTasks(){
		
			$fixedParams = "'{$this->type}', '{$this->code}', '{$this->modifier}'";
			$extraParams = $this->toJson($this->params);
			
			$cmd = "Modules.setImgPath('".MODULES_IMAGES."');";
			$cmd .= "Modules.initialize({$fixedParams}, {$extraParams});";
			
			$this->AjaxEngine->addScript( $cmd );
			
		}
		

##################################
############# CONFIG #############
##################################

		/**
		 * Initialize and validate data provider and main vars
		 */
		private function initialize(){
		
			# Make sure the type of page is valid
			if( !is_callable(array($this, $this->type)) ){
				return $this->recordError('ModulesBase: wrong handler type provided');
			}
			
			# Attempt to load data provider
			$class = "Mod_{$this->code}";
			$path = MOD_DEFINITIONS_PATH."{$class}.mod.php";
			if( is_file($path) ) require_once( $path );
			
			if( !class_exists($class) ){
				return $this->recordError('ModulesBase error: data provider not found');
			}
			$this->DP = new $class($this->type, $this->code, $this->modifier);
			
			# Process data and set common vars from Data Provider
			$this->setCommonProperties();
			
			# Store most common or general vars for the template engine
			$this->feedTemplate();
			
		}
		
		/**
		 * @overview: common elements to be registered in the template engine.
		 *            These elements are not expected to require long processing
		 *            times or high CPU/RAM usage. It's basically configuration
		 *            hardcoded in the Data Provider.
		 * @returns: NULL
		 */
		private function setCommonProperties(){
			
			# Get and set keys, fields and tools
			$this->provideKeys();
			$this->provideFields();
			$this->provideTools();
			
		}
		
		/**
		 * @overview: common elements to be registered in the template engine.
		 *            These elements are not expected to require long processing
		 *            times or high CPU/RAM usage. It's basically configuration
		 *            hardcoded in the Data Provider.
		 * @returns: NULL
		 */
		private function feedTemplate(){
		
			# General and presentational
			$this->assign('cycleValues', '#eaeaf5,#e0e0e3,#e5e6eb');
			$this->assign('MODULES_TEMPLATES', MODULES_TEMPLATES);
			$this->assign('MODULES_IMAGES', MODULES_IMAGES);
			$this->assign('DEVELOPER_MODE', defined('DEVELOPER_MODE') ? DEVELOPER_MODE : false);
			
			# Internal attributes
			$this->assign('type', $this->type);
			$this->assign('code', $this->code);
			$this->assign('modifier', $this->modifier);
			
			$strParams = is_string($this->params) ? $this->params : $this->toJson($this->params);
			$this->assign('params', $strParams);
			
			# Common attributes
			$this->assign('name', $this->DP->getName());
			$this->assign('plural', $this->DP->getPlural());
			$this->assign('tipField', $this->DP->getTipField());
		
		}


##################################
######### GET - SETTERS ##########
##################################
		
		/**
		 * Fields configuration can be set giving a string as attribute, which
		 * is interpreted as its name (with all other attributes to defaults),
		 * and most attributes are optional. Just make sure the configuration
		 * is valid: well-formatted and with all fields set to avoid warnings.
		 */
		private function provideFields(){
			
			# Fields configuration (all defined fields for this module)
			$base = $this->DP->getFields();
			if( empty($base) ){
				return $this->recordError('ModulesBase error: no fields defined');
			}
			if( !is_array($base) ){
				return $this->recordError('ModulesBase error: invalid fields definition');
			}
			
			# Accept field codes '>', used in pages for presentational purposes
			$base += array('>' => NULL);
			
			# Fields for current page
			$method = 'get'.ucfirst($this->type).'Fields';
			if( method_exists($this->DP, $method) ){
				$inclFields = $this->DP->{'get'.ucfirst($this->type).'Fields'}();
			}
			if( empty($inclFields) ){
				return $this->recordError('ModulesBase error: requested page is not available');
			}
			if( is_string($inclFields) ) $inclFields = array( $inclFields );
			
			# Import $base into $fields (only for fields defined for this page)
			$fields = @array_intersect_key(array_flip($inclFields), $base);
			foreach( $fields as $key => &$field ) $field = $base[$key];
			
			if( empty($fields) ){
				return $this->recordError('ModulesBase error: could not retrieve fields for this element');
			}
			
			# Fill and/or fix each field's attributes
			foreach( $fields as $id => &$atts ){
				# Accept strings as attributes, assuming it's the name alone
				if( is_string($atts) ) $atts = array('name' => $atts);
				if( !isset($atts['name']) ) $atts['name'] = '';
				# Set unnamed and key fields as hidden unless explicitly set otherwise
				if( in_array($id, $this->keys) || empty($atts['name']) ){
					if( !isset($atts['hidden']) || $atts['hidden'] !== false ){
						$atts['hidden'] = true;
					}	
				}
				if( !isset($atts['hidden']) ) $atts['hidden'] = false;
				# Make sure type is defined
				if( empty($atts['type']) || !in_array($atts['type'], $this->fieldTypes) ){
					$atts['type'] = 'text';
				}
			}
			
			return $this->fields = $this->assign('fields', $fields);
		
		}
		
		/**
		 * We can accept strings instead of an array of key fields, but then
		 * we need to make it an array (with one single element)
		 */	
		private function provideKeys(){
			
			$keys = (array)$this->DP->getKeys();
			
			return $this->keys = $this->assign('keys', $keys);
			
		}

		private function provideTools(){
		
			$base = $this->toolsBase;
			
			# If getTools is not callable, no tools will be available
			if( !is_callable(array($this->DP, 'getTools')) ){
				return $this->tools = $this->assign('tools', array());
			}
			
			# Get defined tools (list of tool codes) and fix input if needed
			$list = (array)$this->DP->getTools();
			
			# Extend $base with other attributes to build tools array
			$tools = array();
			foreach( $base as $id => &$axn ){
				if( in_array($id, $list) ) $tools[$id] = $axn;
			}
			
			return $this->tools = $this->assign('tools', $tools);
		
		}


##################################
############# TOOLS ##############
##################################
		
		/**
		 * Takes a row or col of data, and builds a unique ID for that
		 * item based on that item's values for defined key fields
		 */
		protected function keysArray2String( $item ){
		
			if( !is_array($item) ){
				if( count($this->keys) != 1 ) return NULL;
				$item = array($this->keys[0] => $item);
			}
		
			foreach( $this->keys as $key ){
				if(isset($item[$key]) ) $keysArr[] = $item[$key];
				else return NULL;
			}
			
			return isset($keysArr) ? join('__|__', $keysArr) : '';
			
		}
	
		protected function toJson( $arr=array() ){
			
			if( !is_array($arr) || !count($arr) ) return '{}';
			foreach( $arr as $k => $v ){
				$json[] = '"'.$k.'":'.(is_array($v)
					? $this->toJson($v)
					: (is_numeric($v) ? $v : '"'.addslashes($v).'"')
				);
			}
			
			return '{'.join(",", $json).'}';
		
		}


##################################
############ TEMPLATE ############
##################################
		
		/**
		 * Returns an HTML string from a template, after assigning all vars
		 * passed as $data.
		 */
		protected function fetch($name, $data=array()){
		
			# Register all stored vars in the Template Engine
			foreach( $data + $this->vars as $k => $v ) $this->TemplateEngine->assign($k, $v);
			
			$name = preg_replace('/\.tpl$/', '', $name);
			if( !is_file(MODULES_TEMPLATES.$name.'.tpl') ) $name = 'error';
			$this->TemplateEngine->assign('pathToTemplate', MODULES_TEMPLATES.$name.'.tpl');
			
			return $this->TemplateEngine->fetch( 'global.tpl' );
		
		}
		
		protected function assign($var, $val=NULL){
			
			return $this->vars[$var] = $val;
			
		}
		
		protected function clearVar( $var=NULL ){
		
			if( is_null($var) ) $this->vars = array();
			else unset( $this->vars[$var] );
			
		}


##################################
############# ERRORS #############
##################################

		protected function recordError( $err ){
		
			$this->recordedError = $this->recordedError
				? $this->recordedError . '<br />' . $err
				: $err;
			
		}
		
		
		/**
		 * Returns a formatted error (HTML string)
		 */
		protected function Error( $msg ){
			
			$Error = new ModulesError;
			
			return $Error->fetch( $msg );
			
		}


##################################
########### DEBUGGING ############
##################################
		
		protected function seeVars(){
		
			return var_export($this->vars, true);
		
		}
	
	}

?>