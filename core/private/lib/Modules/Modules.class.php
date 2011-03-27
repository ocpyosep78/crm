<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

/**
 * @name: Class Modules
 * @description: automates the creation of common pages for a defined module
 * @author: Diego Barreiro <diego.bindart@gmail.com>
 * @created: mar 2011
 * 
 * @overview: given a module definition class we call Handler, Modules can
 *            retrieve an element's HTML for the following:
 *            	- createPage: a page to create new items for that module
 *            	- editPage: a page to edit this module's items
 *              - infoPage: a page to show all relevant info about an item
 *              - comboList: a SELECT combo to jump between items' infoPages
 *              - simpleList: a compact list with create/edit/info embedded
 *              - commonList: a regular tabular list linking to other pages
 *                            of the module, with pagination, search by
 *                            column and tools for items in each row
 * 
 * @in-depth: The availability of each element depends on the content of the
 *            corresponding definition class. See _template.mod.php in
 *            MODULES_TEMPLATES for more info on how to enable/disable each
 *            type of element.
 *            Strictly speaking, each element is not a page but a piece of
 *            HTML that might be combined with others to build a page. For
 *            example, infoPage, commonList and editPage might include a
 *            comboList's HTML above their own HTML, adding together to form
 *            a page.
 * 
 * @structure: - Modules: is the only class of the group that has an interface
 *             for the outside. All its methods are public.
 *             - PageChecker: validates a page type for a given module. It
 *             can parse page names to get module's code and element's type
 *             (i.e. usersInfo to module:users, type:info) and check whether
 *             a given page is available for a particular module.
 *             - PageCreator: is called by Modules only, and it handles page
 *             creation, based on the elements a page should include (i.e.
 *             commonList page should have a comboList first, then a
 *             commonList).
 *             - ModulesBase: it's the ancestor of all Handlers, providing
 *             common methods and defaults, and storing commong attributes.
 *             - ModulesDefaults: ancestor of definition classes, offers
 *             defaults and handling for unspecified methods.
 * 
 * @Modules.class.php:
 *             #canBuildPage(str:$page) returns true/false meaning a page
 *             		is available (depending on module's definition)
 *             #getPage(str:$name, str:$modifier, mixed:$params) returns
 *             		a page's HTML (one or more elements combined)
 *             #printPage(str:$name, str:$modifier, mixed:$params) prints a
 *             		page's HTML retrieved by calling #getPage, using the
 *             		configured Ajax Engine's #write method (by default tied
 *             		to Xajax#assign) to fill PAGE_CONTENT_BOX's innerHTML
 *             		(where PAGE_CONTENT_BOX is page's given box's HTML id).
 *             		When done fetching, it calls Handler#doTasks to have
 *             		each element do further tasks after printing (i.e. for
 *             		calling JS functions, adding styles, etc.)
 *             #ajaxPrintPage(str:$name, str:$modifier, mixed:$params) used
 *                  to print elements' HTML through ajax. $params.['writeTo']
 *                  is the target element's ID. If omitted, the target is the
 *                  page main element (PAGE_CONTENT_BOX constant).
 * 
 * @modules: adding modules requires only a definition class that follows the
 *           template's structure. Within the template you'll find documentation
 *           on each method's purpose and expected outcome, as well as what would
 *           happen if the method is commented out (there's usually a plan B). Most
 *           methods are optional, so a regular definition file could take just a
 *           few minutes, and it'd result in a whole bunch of new pages and tools.
 * 
 * @plugins: due to the way things are handled, it is easy to extend this
 *           library by adding new kinds of elements and new Handlers to build
 *           them.
 */


	define('MODULES_TEMPLATES', 'core/private/lib/Modules/templates/');
	define('MODULES_IMAGES', 'core/private/lib/Modules/static/images/');
	

	require_once( CONNECTION_PATH );
	
	require_once( dirname(__FILE__).'/engines/ajax.engine.php' );
	require_once( dirname(__FILE__).'/engines/template.engine.php' );
	
	require_once( dirname(__FILE__).'/lib/ModulesBase.class.php' );
	require_once( dirname(__FILE__).'/lib/ModulesDefaults.class.php' );
	
	require_once( dirname(__FILE__).'/lib/PageChecker.class.php' );
	require_once( dirname(__FILE__).'/lib/PageCreator.class.php' );
	

	class Modules{
	
		private $PageChecker;
		private $PageCreator;
		private $AjaxEngine;
		
		public function __construct(){
		
			$this->PageChecker = new PageChecker;
			$this->PageCreator = new PageCreator;
			$this->AjaxEngine = new Modules_ajaxEngine;
		
		}
	
		/**
		 * Whether a page can be built. Takes a single argument that's assumed
		 * to be a page name (i.e. usersInfo, customersEdit, etc.).
		 */
		public function canBuildPage( $page ){
			
			return $this->PageChecker->canBuildPage( $page );
			
		}
	
		/**
		 * @overview: Given a well-formatted element name, retrieves that element's
		 *            HTML if defined.
		 *            This will not produce any output or storage, it's just plain HTML.
		 * @returns: an HTML string
		 */
		public function getElement($name, $modifier=NULL, $params=NULL){
		
			list($code, $type) = $this->PageChecker->parsePageName( $name );
			
			return $this->PageCreator->getElement($type, $code, $modifier, $params);
			
		}
	
		/**
		 * @overview: Given a well-formatted element name, retrieves that element's
		 *            page if defined. The page might include other secondary elements,
		 *            like comboLists or auxiliary lists.
		 *            This will not produce any output or storage, it's just plain HTML.
		 * @returns: an HTML string
		 */
		public function getPage($name, $modifier=NULL, $params=NULL){
			
			list($code, $type) = is_array($name)
				? $name
				: $this->PageChecker->parsePageName( $name );
			
			return $this->PageCreator->getPage($type, $code, $modifier, $params);
			
		}
	
		/**
		 * @overview: Gets a page's HTML and prints it
		 * @returns: NULL
		 */
		public function printPage($name, $modifier=NULL, $params=NULL){
			
			echo $this->getPage($name, $modifier, $params);
		
		}
	
		/**
		 * @overview: Gets a page's HTML and prints it through the Ajax Engine's write
		 *            method (by default it maps to Xajax#assign). It then calls
		 *            PageCreator#doTasks for further actions (adding scripts, running
		 *            scripts, modifying other parts of the page, etc.).
		 * @returns: an ajax response object
		 */
		public function ajaxPrintPage($name, $modifier=NULL, $params=NULL){
			
			$writeTo = empty($params['writeTo']) ? PAGE_CONTENT_BOX : $params['writeTo'];
			$HTML = $this->getPage($name, $modifier, $params);
			
			$this->AjaxEngine->write($writeTo, $HTML);
			
			$this->PageCreator->doTasks();
			
			return $this->AjaxEngine->getResponse();
		
		}
	
	}

?>