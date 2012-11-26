<?php

	class Tabs{

		private $page;
		private $module;

		private $template=NULL;		/* For prefetched templates */
		private $HTML=NULL;			/* For prefetched templates */

		private $tabs=NULL;



		public function __construct(){
			$this->page = oNav()->currentPage();
			$this->module = oNav()->getCurrentModule();
		}

		public function setPage($page)
		{
			$this->page = $page;
			$this->module = Access::pageArea($page);
		}

		public function getTabs()
		{
			# We need to search for tabs only on first call
			if (!is_null($this->tabs))
			{
				return $this->tabs;
			}

			# Include tabs script for this module
			$tabs = $this->includeTabsScript();

			if (empty($tabs))
			{
				return $this->tabs = array();
			}

			foreach ($tabs as $tab => &$atts)
			{
				# Accept attributes with just a string (screenname for the tab)
				if (!is_array($atts))
				{
					$atts = array('name' => $atts);
				}

				# Check the function to draw this tab exists
				if (!function_exists($func="tab_{$this->page}_{$tab}"))
				{
					unset($tabs[$tab]);
					continue;
				}

				# See if we need to take permissions into account
				if (isset($atts['permit']) && Access::cant($atts['permit']))
				{
					unset($tabs[$tab]);
				}
			}

			return $this->tabs = isset($tabs) ? $tabs : [];
		}

		/**
		 * Passing a template path to this function makes the object stop looking for the
		 * right template (given by the module, page and tab codes). It simply fills the
		 * tabsContent box with received template.
		 */
		public function useThisTemplate( $template ){

			$this->template = $template;

		}

		/**
		 * Passing an HTML string to this function makes the object stop looking for the
		 * right template (given by the module, page and tab codes). It simply fills the
		 * tabsContent box with received HTML string.
		 */
		public function useThisHTML( $HTML='' ){

			$this->HTML = $HTML;

		}

		/**
		 * @overview: Initializes a page with Tabs in it. Basically, it prints
		 *            a fixed part of the page, and adds below the tabs, fully
		 *            functional.
		 * @returns: XajaxResp object
		 * @notes: the only parameter, $HTML has 3 possible states, each defining
		 *         a different behaviour
		 *         		- $HTML == NULL means the page will be automatically found
		 *                and fetched (needed data comes from oNav).
		 *         		- $HTML == false means the page should not be overwritten.
		 *                Tabs are appended below.
		 *         		- $HTML is a string, then it's used as HTML for the fixed
		 *                part of the page.
		 *         In practice, anything that's not NULL and not a string will act
		 *         as boolean false.
		 */
		public function start($HTML=NULL)
		{
			$tab = array_shift(array_keys($this->getTabs()));

			# If $HTML parameter is NULL, we automatically add the fixed page...
			if (is_null($HTML))
			{
				$path = realpath(TEMPLATES_PATH . "/{$this->module}/{$this->page}.tpl");
				$path && addAssign('main_box', 'innerHTML', oSmarty()->fetch($path));
			}
			# ...else if it's a string we use it as page content
			elseif(is_string($HTML))
			{
				addAssign('main_box', 'innerHTML', $HTML);
			}

			# Display the tabs skeleton below fixed content
			$content = oSmarty()->fetch(dirname(__FILE__).'/templates/tabs.tpl');
			jQuery('#main_box')->html($content);

			# Now we're ready to load the first tab through regular method switchTab
			return $this->switchTab($tab);
		}

		public function switchTab($tab)
		{
			if (!$this->getTabs())
			{
				return oXajaxResp();
			}

			# Get all passed params, and forward all but the tabCode (first one)
			$allowed = $tab && (array_search($tab, array_keys($this->getTabs())) !== false);

			# Assign it in Smarty so the right template can be included
			oSmarty()->assign('tab', $tab);
			oSmarty()->assign('tabs', $this->getTabs());

			# Run the tab's function (so it can assign smarty vars)
			if (!$allowed)
			{
				return say('No se puede cargar la pestaña solicitada.');
			}

			# Call tab handler
			$fn = "tab_{$this->page}_{$tab}";
			$postCalls = call_user_func_array($fn, oNav()->getCurrentAtts());

			# Get content if template exists and no HTML string was set by the tab generating function
			$content = !is_null($this->HTML)
				? $this->HTML
				: $this->getTpl( $this->template ? $this->template : $tab );

			# Update tab area content
			addAssign('tabContent', 'innerHTML', $content);
			addAssign('tabButtons', 'innerHTML', $this->template('tabButtons'));

			# Javascript to be run after building content (one string or an array of strings)
			if( $postCalls ){
				if( is_string($postCalls) ) $postCalls = array( $postCalls );
				foreach( $postCalls as $call ) if( is_string($call) ) addScript( $call );
			}

			return oXajaxResp();
		}

		private function includeTabsScript()
		{
			$path = MODS_PATH . "/{$this->module}/tabs.php";

			# Include tabs script for this module
			is_file($path) && ($tabs = require_once $path);

			# $tabs === 1 means file was included but returned nothing
			return (isset($tabs) && is_array($tabs)) ? $tabs : false;

		}

		private function getTpl($tab)
		{
			return ($path = realpath(TEMPLATES_PATH . "/{$this->module}/{$this->page}_{$tab}.tpl"))
				? oSmarty()->fetch( $path )
				: '';

		}

		private function template( $which ){

			return is_file($path=dirname(__FILE__)."/templates/{$which}.tpl")
				? oSmarty()->fetch( $path )
				: '';

		}

	}