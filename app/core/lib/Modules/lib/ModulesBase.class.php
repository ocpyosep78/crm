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
	 * outside): #getPage and #doTasks().
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
		protected $configIntegrity;	/* The result of reading config (boolean) */
		
		private $TemplateEngine;
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
			$this->TemplateEngine = new Modules_templateEngine;
			$this->vars = array();
			
			# Store and pass to template engine main vars and objects
			$this->dataProvider = $this->setDataProvider();
			$this->configIntegrity = $this->dataProvider === true
				? $this->readConfig()
				: false;
		
		}
		
		protected function foundError(){
			
			# Make sure the type of page is valid
			if( !method_exists($this, $this->type) ) return $this->displayError('ModulesBase: wrong type.');
			if( $this->dataProvider !== true ) return $this->displayError( $this->dataProvider );
			if( $this->configIntegrity !== true ) return $this->displayError( $this->configIntegrity );
			
			return false;
			
		}

##################################
############# PUBLIC #############
##################################
		
		/**
		 * Method supposed to be overriden by this class' children.
		 */
		public function getPage(){ return ''; }
		public function doTasks(){}
		
		public function getComboList( $selected=NULL ){
		
			return method_exists($this, 'comboList')
				? $this->comboList( $selected )
				: $this->Creator->getPage('comboList', $this->code, $this->modifier, $selected);
			
		}

##################################
########### PROTECTED ############
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
		
		protected function getInfoPageData( $keys ){
		
			$filters = $this->sanitizeInfoKeys( $keys );
			
			# Get the right SQL, falling back to lists data if needed
			$sql = $this->DP->getInfoPageData( $filters );
			if( !$sql ) $altSQL = $sql = $this->DP->getCommonListData();
			if( !$sql ) $altSQL = $sql = $this->DP->getSimpleListData();
			if( !$sql ) return NULL;
			
			# Clear ORDER BY, GROUP BY and LIMIT clauses, and strip linefeeds
			# This is necessary only if we got the query from another page's
			if( !empty($altSQL) ){
				$sql = preg_replace('/\s/', ' ', $sql);
				$sql = preg_replace('/(GROUP BY|ORDER BY|LIMIT).+$/', '', $sql);
				$sql .= ( !strstr(strtoupper($sql), 'WHERE ') ? 'WHERE ' : 'AND ' ).
					" {$this->array2filter($filters)} LIMIT 1";
			}
			
			return $this->query($sql, 'row');
			
		}
		
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
		
		/**
		 * Shortcut to return a generic error page
		 */
		protected function displayError( $msg=NULL ){
		
			return $this->fetch('404', array('msg' => $msg));
			
		}
		

##################################
############ PRIVATE #############
##################################

############# CONFIG #############
		
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
		protected function readConfig(){
			
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

############# FIX VARS #############
		
		/**
		 * We can accept strings instead of an array of key fields, but then
		 * we need to make it an array (with one single element)
		 */
		private function sanitizeAndStoreKeys( $keys ){
		
			if( !is_array($keys) ) $keys = array($keys);
			
			$this->keys = $keys;
		
		}
		
		
		/**
		 * Fields configuration can be set giving a string as attribute, which
		 * is interpreted as its name (with all other attributes to defaults),
		 * and most attributes are optional. Just make sure the configuration
		 * is valid: well-formatted and with all fields set to avoid warnings.
		 */
		private function sanitizeAndStoreFieldsCfg( $fieldsCfg ){
			
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
		private function sanitizeAndStoreFields( $fields ){
		
			foreach( $fields as &$field ){
				if( isset($this->fieldsCfg[$field]['name']) && $this->fieldsCfg[$field]['name'] === '' ){
					$this->fieldsCfg[$field]['hidden'] = true;
				}
			}
			
			$this->fields = $fields;
		
		}
		
		private function sanitizeInfoKeys( $keys ){
			
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

############# CACHE #############
		
		/**
		 * Retrieving data (usually from the database) can be time- and
		 * CPU-consuming, so we attempt to cache retrieved data that can
		 * be reused if filters and modifier are still the same as stored.
		 */
		private function setDataCache($code, $data, $filters=array()){
			
			$this->dataCache[$code] = array(
				'modifier'	=> $this->modifier,
				'filters'	=> $filters,
				'data'		=> $data,
			);
			
			return $data;
			
		}
		
		/**
		 * Retrieving cached data, within one script run (not in session)
		 * (see setDataCache's comment for more info)
		 */
		private function getDataCache($code, $filters=array()){
		
			return (isset($this->dataCache[$code])
					&& $this->dataCache[$code]['modifier'] === $this->modifier
					&& $this->dataCache[$code]['filters'] === $filters)
						? $this->dataCache[$code]['data']
						: NULL;
						
		}
		
		/**
		 * @overview: Gets a data array from user-configured sql query
		 * @returns: - on success, an array
		 *           - on error, false
		 *           - if missing, NULL
		 */
		protected function getListData($code, $filters=array()){
			
			# See if we've got it stored already
			$cachedData = $this->getDataCache($code, $filters);
			if( !is_null($cachedData) ) return $cachedData;
		
			$method = 'get'.ucfirst($code).'ListData';
			$formatAs = $code == 'combo' ? 'asHash' : 'asList';
			
			$sql = $this->DP->$method( $filters );
			$data = $sql ? $this->$formatAs($sql, $this->keys) : NULL;
			
			return $this->setDataCache($code, $data, $filters);
			
		}

############# DEBUGGING #############
		
		protected function seeVars(){
		
			return var_export($this->vars, true);
		
		}
	
	}

?>