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
	
		protected $type;			/* Type of page, for page-handlers with multiple types (i.e. Module_Lists) */
		protected $code;			/* Unique identifier for this module */
		protected $modifier;		/* Some modules might have different behaviours depending on a parameter */
		
		protected $fieldsCfg;		/* List of fields for this module, with their attributes */
		protected $fields;			/* List of fields for current page */
		protected $keys;			/* Key fields for current module */
		
		protected $dataCache;		/* Cached lists data in case we need to reuse it */
		
		protected $Creator;			/* Object of class PageCreator that built this object's child */
		protected $DP;				/* Data Provider for this module (user-configured) */
		
		protected $dataProvider;	/* Whether the data provider could be found (boolean) */
		
		protected $AjaxEngine;
		protected $TemplateEngine;
		private $vars;				/* Registers vars to be used in the templates */
		

##################################
########## CONSTRUCTOR ###########
##################################

		public function __construct($type, $code, $modifier, $Creator){
		
			parent::__construct();
			
			$this->Creator = $Creator;
		
			# Main parameters for this module
			$this->type = $type;
			$this->code = $code;
			$this->modifier = $modifier;
			
			# Initialize only
			$this->fieldsCfg = array();
			$this->fields = array();
			$this->keys = array();
			$this->listData = NULL;
			
			# Template engine
			$this->AjaxEngine = new Modules_ajaxEngine;
			$this->TemplateEngine = new Modules_templateEngine;
			$this->vars = array();
		
		}


##################################
############# PUBLIC #############
##################################
		
		/**
		 * @overview: This is the class' main method by far. Module Handlers (descended
		 *            from this one) have here their initialization and validation of
		 *            input. It is ModulesBase, through its few public methods, that
		 *            calls methods in Handlers (they're all protected or private).
		 *            It is important to notice that Handlers have no knowledge of pages
		 *            or how the returned HTML string is going to be presented. Handlers
		 *            only know about atomic elements, that PageCreator (or another
		 *            class) might combine or not, in order to make pages.
		 * @returns: an HTML string
		 */
		public function getElement( $params=NULL ){
		
			$integrityCheckResult = $this->initialize();
			
			# Let the right method handle the rest, depending on $this->type
			return ($integrityCheckResult === true)
				? $this->{$this->type}( $params )
				: $integrityCheckResult;
			
		}
		
		/**
		 * 
		 */
		public function runAjaxCall(){
		
			$args = func_get_args();
			
			$integrityCheckResult = $this->initialize();
			
			# Let the right method handle the rest, depending on $this->type
			if($integrityCheckResult === true){
				$res = call_user_func_array(array($this, $this->type), $args);
				if( is_null($res) ) return $this->doTasks();
			}
			else $this->AjaxEngine->displayError( $integrityCheckResult );
			
		}
		
		/**
		 * 
		 */
		public function doTasks( $params=NULL ){
		
			$fixedParams = "'{$this->type}', '{$this->code}', '{$this->modifier}'";
			$extraParams = $this->toJson($params);
		
			$cmd = "Modules.initialize({$fixedParams}, {$extraParams});";
			
			$this->AjaxEngine->addScript( $cmd );
			
		}
		

##################################
############# CONFIG #############
##################################

		/**
		 * Initialize and validate vars and config
		 */
		public function initialize(){
		
			# Make sure the type of page is valid
			if( !is_callable(array($this, $this->type)) ){
				return $this->displayError('ModulesBase: wrong type.');
			}
			
			# Attempt to load data provider
			$this->dataProvider = $this->setDataProvider();
			if( $this->dataProvider !== true ){
				return $this->displayError( $this->dataProvider );
			}
			
			# Store and pass to template engine main vars and objects
			$configIntegrity = $this->readConfig();
			if( $configIntegrity !== true ){
				return $this->displayError( $configIntegrity );
			}
			
			return true;
			
		}
		
		/**
		 * Validate and store data provider class for this module.
		 * Returns the data provider on success, false otherwise.
		 */
		private function setDataProvider(){
			
			$class = "Mod_{$this->code}";
			$path = MODULES_PATH."{$class}.mod.php";
			
			if( !is_file($path) ) return 'ModulesBase error: data provider not found';
			
			require_once( $path );
			$this->DP = new $class($this->type, $this->code, $this->modifier);
			
			return true;
			
		}
		
		/**
		 * Common elements to be registered in the template engine or stored.
		 * These elements are not expected to require long processing times or
		 * high CPU/RAM usage. It's basically configuration hardcoded in the
		 * Data Provider.
		 * @returns: returns true if everything's in place, or an error string
		 *           if a required element is missing or corrupted
		 */
		private function readConfig(){
			
			# Internal attributes
			$this->assign('type', $this->type);
			$this->assign('code', $this->code);
			$this->assign('modifier', $this->modifier);
			
			# Common attributes
			$this->assign('name', $this->DP->getName());
			$this->assign('plural', $this->DP->getPlural());
			$this->assign('tipField', $this->DP->getTipField());
			
			# Keys
			$keys = $this->DP->getKeys();
			$this->sanitizeAndStoreKeys( &$keys );
			$this->assign('keys', $keys);
			
			# Fields configuration
			$fieldsCfg = $this->DP->getFields();
			if( empty($fieldsCfg) || !is_array($fieldsCfg)){
				return 'ModulesBase error: no fields defined';
			}
			$this->sanitizeAndStoreFieldsCfg( &$fieldsCfg );
			$this->assign('fieldsCfg', $fieldsCfg);
			
			# Fields for current page
			if( !method_exists($this->DP, $method='get'.ucfirst($this->type).'Fields') ){
				return 'ModulesBase error: requested type does not exist';
			}
			$fields = $this->DP->{'get'.ucfirst($this->type).'Fields'}();
			if( is_null($fields) ){
				return 'ModulesBase error: requested page does not exist';
			}
			if( !is_array($fields) ) $fields = array( $fields );
			$this->sanitizeAndStoreFields( &$fields );
			$this->assign('fields', $fields);
			
			return true;
			
		}


##################################
############ TEMPLATE ############
##################################
		
		/**
		 * Returns an HTML string from a template, after assigning all vars
		 * passed as $data.
		 */
		protected function fetch($name, $data=array()){
		
			$name = preg_replace('/\.tpl$/', '', $name);
		
			foreach( $data + $this->vars as $k => $v ) $this->TemplateEngine->assign($k, $v);
			
			if( !is_file(MODULES_TEMPLATES_PATH."{$name}.tpl") ) $name = '404';
		
			return $this->TemplateEngine->fetch( "{$name}.tpl" );
		
		}
		
		protected function assign($var, $val=NULL){
			
			$this->vars[$var] = $val;
			
		}
		
		protected function clearVar( $var=NULL ){
		
			if( is_null($var) ) $this->vars = array();
			else unset( $this->vars[$var] );
			
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
		
		/**
		 * We can accept strings instead of an array of key fields, but then
		 * we need to make it an array (with one single element)
		 */
		protected function sanitizeAndStoreKeys( $keys ){
		
			if( !is_array($keys) ) $keys = array($keys);
			
			$this->keys = $keys;
		
		}
		
		
		/**
		 * Fields configuration can be set giving a string as attribute, which
		 * is interpreted as its name (with all other attributes to defaults),
		 * and most attributes are optional. Just make sure the configuration
		 * is valid: well-formatted and with all fields set to avoid warnings.
		 */
		protected function sanitizeAndStoreFieldsCfg( $fieldsCfg ){
			
			foreach( $fieldsCfg as $key => &$atts ){
				# Accept strings as attributes, assuming it's the name alone
				if( is_string($atts) ) $atts = array('name' => $atts);
				# Make sure all got a name defined, even if empty
				if( !isset($atts['name']) || !is_string($atts['name']) ){
					$atts['name'] = '';
				}
				# Set key fields as hidden unless explicitly set otherwise
				if( in_array($key, $this->keys) ){
					if( !isset($atts['hidden']) || $atts['hidden'] !== false ){
						$atts['hidden'] = true;
					}	
				}
				# Make sure all got hidden attribute defined (defaults to false)
				if( !isset($atts['hidden']) ) $atts['hidden'] = false;
				# Set type = text if nothing set
				if( empty($atts['type']) ) $atts['type'] = 'text';
			}
			
			$this->fieldsCfg = $fieldsCfg;
			
		}
		
		/**
		 * Force fields that do not have screen names to be hidden.
		 */
		protected function sanitizeAndStoreFields( $fields ){
		
			foreach( $fields as &$field ){
				if( isset($this->fieldsCfg[$field]['name']) && $this->fieldsCfg[$field]['name'] === '' ){
					$this->fieldsCfg[$field]['hidden'] = true;
				}
			}
			
			$this->fields = $fields;
		
		}
		
		protected function sanitizeInfoKeys( $keys ){
			
			# Build and apply ID filter (check integrity first)
			if( !is_array($keys) ){
				# Only one key and one ID
				if( count($this->keys) != 1 ) return false;
				else return array($this->keys[0] => $keys);
			}
			else{
				foreach( $this->keys as $key ) if( empty($keys[$key]) ) return false;
				foreach( array_keys($keys) as $key ) if( !in_array($key, $this->keys) )return false;
				return $keys;
			}
			
		}
	
		protected function toJson( $arr=array() ){
			
			if( !is_array($arr) || !count($arr) ) return '{}';
			foreach( $arr as $k => $v ){
				$json[] = "'{$k}':".(is_array($v) ? toJson($v) : (is_numeric($v) ? $v : "'".addslashes($v)."'"));
			}
			
			return '{'.join(",", $json).'}';
		
		}


##################################
######### ERROR HANDLING #########
##################################
		
		/**
		 * Shortcut to return a generic error page
		 */
		protected function displayError( $msg=NULL ){
		
			return $this->fetch('404', array('msg' => $msg));
			
		}


##################################
########### DEBUGGING ############
##################################
		
		protected function seeVars(){
		
			return var_export($this->vars, true);
		
		}
	
	}

?>