<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	if( !defined('EMPTY_TPL') ) define('EMPTY_TPL', '__noTemplate.tpl');
	
	require_once( dirname(__FILE__).'/Page.class.php' );


	class PageCfg{
		
		private $Page;
		
		
		public function __construct(){
			$this->Page = new Page();
		}
		
		public function getPage(){
			return $this->Page;
		}
		
		public function set_content( $tpl=NULL ){
			$template = $tpl ? win2unix($this->fillExtension($tpl, 'tpl')) : EMPTY_TPL;
			$this->set('template', $template);
			$this->set('content', oSmarty()->fetch($template));
		}
		
		public function set_appName( $str=APP_NAME ){
			$this->set('appName', $str);
		}
		
		public function set_appTitle( $title=NULL ){
			$this->set('appTitle', $title ? APP_NAME." - {$title}" : APP_NAME);
		}
		
		public function set_appImg( $img=APP_IMG ){
			$this->set('appName', $str);
		}
		
		public function add_modules( $mod=NULL, $name=NULL ){
			if( is_array($mod) ) foreach( $mod as $key => $val ) $this->add_modules($key, $val);
			elseif( $mod ) $this->append('modules', $mod, $name);
		}
		
		public function set_module( $mod ){
			$this->set('module', $mod);
		}
		
		public function add_navButtons($code=NULL, $isPage=true, $altName=NULL){
			if( !is_file($img=IMG_PATH."navButtons/{$code}.gif")
			  && !is_file($img=IMG_PATH."navButtons/{$code}.png") ){
				trigger_error("No se encontró imagen del módulo {$code} ({$img}).");
			}
			$mods = $this->get( 'modules' );
			$name = $isPage ? $mods[$code]['name'] : ($altName ? $altName : $code);
			$action = $code ? ($isPage ? "getPage(event, \"{$code}\")" : "xajax_{$code}()") : '';
			$navButton = array('name' => $name, 'action' => $action);
			$this->append('navButtons', $code, $navButton);
		}
		
			
		/**
		 * There are 2 ways to call this function:
		 *	One item: oPageCfg()->add_menuItems(area, item, page, action);
		 *	One group: oPageCfg()->add_menuItems(area, array of items minus group );
		 */
		public function add_menuItems($area, $item, $code=NULL, $isPage=true){
		
			if( !$area || !$item ) return;

			if( is_array($item) ){
				foreach( $item as $oneItem ){
					if( !is_array($oneItem) ) continue;
					$oneItem += array(NULL, NULL, NULL);	# Make sure to fill the 3 keys in the array
					$this->add_menuItems($group, $oneItem[0], $oneItem[1], $oneItem[2]);
				}
			}elseif($code || devMode()){
				$menuItems = $this->get( 'menuItems' );
				$action = $code ? ($isPage ? "getPage(event, \"{$code}\")" : "xajax_{$code}()") : '';
				$menuItems[$area][] = array('code' => $code, 'name' => $item, 'action' => $action);
				$this->set('menuItems', $menuItems);
			}

		}
		
		public function add_pageNav( $part, $action='', $clear=false ){
			if( $clear ) $this->set('pageNav', array());
			if( is_array($part) ) foreach($part as $iPart) $this->add_pageNav( $iPart );
			elseif( $part ){
				$this->append('pageNav', strtolower($part), $action);
				$this->set('page', $part);
			}
		}
		
		public function add_styleSheets( $path=NULL ){
			if( is_array($path) ) foreach( $path as $sPath ) $this->add_styleSheets( $sPath );
			elseif( $path ) $this->append('styleSheets', $this->fillExtension($path, 'css'));
		}
		
		public function add_jScripts( $path=NULL ){
			if( is_array($path) ) foreach( $path as $sPath ) $this->add_jScripts( $sPath );
			elseif( $path ) $this->append('jScripts', $this->fillExtension($path, 'js'));
		}
		
		public function add_jsCode( $code=array() ){
			if( is_array($code) ){
				foreach( $code as $codeLine ) $this->add_jsCode($codeLine);
			}
			elseif( $code ){
				$this->set('jsCode', $this->get('jsCode').str_replace(';;', ';', "{$code};"));
			}
		}
		
		public function add_jsOnLoad( $code=array() ){
			if( is_array($code) ){
				foreach( $code as $codeLine ) $this->add_jsOnLoad($codeLine);
			}
			elseif( $code ){
				$jsOnLoad = $this->get('jsOnLoad');
				$this->set('jsOnLoad', $jsOnLoad.str_replace(';;', ';', "{$code};"));
			}
		}
		
		
		public function get($var, $key=NULL){
			if( $key ) return isset($this->Page->$var[$key]) ? $this->Page->$var[$key] : NULL;
			else return isset($this->Page->$var) ? $this->Page->$var : NULL;
		}
		
		public function set($var, $val){
			$this->Page->$var = $val;
		}
		
		private function append($var, $key, $val=NULL){
			$array = $this->Page->$var;
			if( $val === NULL ) $array[] = $key;
			elseif( $key ) $array[$key] = $val;
			$this->Page->$var = $array;
		}
		
		private function fillExtension($file, $ext){
			return !preg_match("/.".$ext."$/", $file) ? "{$file}.{$ext}" : $file;
		}
		
		
/***************
** P E N D I N G
***************/
		
		
		
		public $debugger			= false;
		public $debugHeader		= '';
		public $debugMsgs			= array();
		public $errMsgs				= array();
		
		public function add_develMsgs( $msg ){
			$this->append('develMsgs', $msg);
		}
	
		public function set_debugger( $activate=true ){
			$this->set('debugger', $activate);
			$this->add_jsCode('DEVELOPER_MODE = '.((int)$activate).';');
			if( !$activate ) return error_reporting( E_ERROR );	
			error_reporting( E_ALL & ~E_DEPRECATED );
			set_error_handler( array(&$this, 'error_handler') );
			oXajax()->statusMessagesOn();
		}


	
		/***************
		** E R R O R   H A N D L I N G
		***************/
		public function error_handler( $errno , $errstr , $errfile , $errline ){
		
			$errNos = array(
				1		=> 'E_ERROR',
				2		=> 'E_WARNING',
				4		=> 'E_PARSE',
				8		=> 'E_NOTICE',
				16		=> 'E_CORE_ERROR',
				32		=> 'E_CORE_WARNING',
				64		=> 'E_COMPILE_ERROR',
				128		=> 'E_COMPILE_WARNING',
				256		=> 'E_USER_ERROR',
				512		=> 'E_USER_WARNING',
				1024	=> 'E_USER_NOTICE',
				2048	=> 'E_STRICT',
				4096	=> 'E_RECOVERABLE_ERROR',
				8192	=> 'E_DEPRECATED',
				16384	=> 'E_USER_DEPRECATED',
				30719	=> 'E_ALL'
			);
			
			$errfile = win2unix($errfile);
			$root = win2unix(APP_PATH);
			
			// Pre-formatting for special errors
			if( strstr($errfile,'temp/es^') ){
				$fp = fopen( $errfile , 'r' );
				$line = fgets( $fp );
				$line = fgets( $fp );
				$fileName = win2unix( preg_replace(
					"/\s*compiled from ([^\s]+) \*\/ \?\>\s+/",
					'$1',
					$line
				) );
				if( substr($errstr,0,18) == 'Undefined index:  ' ){
					$errfile = "{$root}/app/templates/{$fileName}";
					$errline = '0';
					$smartyVar = substr($errstr,18);
					$errstr = 'Undeclared Smarty Variable \''.substr($errstr,18).'\'';
				}else{
					$errfile .= ", line {$errline})<br />&nbsp;&nbsp;&nbsp;&nbsp;";
					$errfile .= "({$root}/app/templates/{$fileName})";
					$errline = '0';
				}
			}
			
			// Warnings, notices and errors to ignore
			if( substr($errstr,0,11) == 'strtotime()' ) return true;
			if( substr($errstr,0,6) == 'date()' ) return true;
			if( substr($errstr,0,10) == 'strftime()' ) return true;
			
			// Final formatting
			switch( $errno ){
				case E_USER_ERROR:
					echo "<b>ERROR</b>: [$errno] $errstr<br />\n";
					echo "  Fatal error on line $errline in file $errfile";
					if( $errline != '0' ) echo ", line $errline";
					echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
					echo "Aborting...";
					exit(1);
				break;
				case E_USER_WARNING:
					$msg = "<b>WARNING</b>: [$errno] $errstr in {$errfile}";
					if( $errline != '0' ) $msg .= ", line {$errline}";
					$msg .= ")";
				break;
				case E_USER_NOTICE:
					$msg = "<b>NOTICE</b>: {$errstr}";
					if(substr($errstr,0,3) != 'SQL'){
						$msg .= " in {$errfile}.";
						if( $errline != '0' ) $msg .= ", line {$errline}";
					}else $msg .= '.';
				break;
				case E_STRICT:		/* Ignore E_STRICT */
					return true;
				break;
				default:
					$name = isset($errNos[$errno]) ? substr($errNos[$errno], 2) : 'UNKNOWN';
					$msg = "{$name}: {$errstr} in {$errfile}";
					if( $errline != '0' ) $msg .= ", line {$errline}";
				break;
			}
			
			// Processing error
			if( !isset($_POST['xajax']) ) $this->printError( $msg );	// Regular call
			elseif( substr($errstr,0,3) != 'SQL' ) addAlert( $msg );	// Xajax call
			
			return true;
		
		}
		
		private function printError( $err ){
			$errMsgs = $this->get( 'debugErrs' ) or $errMsgs = array();
			$errCnt = count( $errMsgs );
			if( $errCnt < 10 ) $errMsgs[] = $err;
			elseif( $errCnt == 10 ) $errMsgs[11] = '.';
			elseif( strlen($errMsgs[11]) < 100 ) $errMsgs[11] .= '.';
			$this->set('errMsgs', $errMsgs);
		}
		
	}
	
?>