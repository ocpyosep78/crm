<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	define('STATIC_DATA_PATH', dirname(__FILE__).'/staticData/');

	require_once( dirname(__FILE__).'/SQL.Lists.class.php' );

/**
 * The goal of this class is to automatize the generation of lists
 * throughout the app as much as possible. This is achieved by separating
 * the process in three big areas: static data, logic, and database.
 *
 * A fourth area would be the script that runs on the client
 * (JS:initializeList), but the JS part does not need to be editted or
 * extended in any way, so it's out of the scope of these notes. Finally,
 * a fifth element is the template part, plus styles. Feel free to play
 * around with these two components, but there should be no need to ever
 * touching them unless you want to change appearence or some behaviour
 * (but then you might also want to look for another library instead =P).
 *
 * Static data is retrieved from separate files ('Data Providers'), one
 * for each kind of object (i.e. customers, users, products, invoices...).
 * These files are found in ./staticData/{$obj}.sd.php (where $obj is the
 * object's code). Read forward to see how these files should be written.
 *
 * Database access is handled by SQL_Lists class (which this one extends)
 * thus giving Lists direct access to SQL_Lists methods as their own. See
 * SQL_Lists header for more information on these.
 *
 * Logic is handled in this script, including making the 3 elements work
 * together.
 *
 * The main method of Lists is printList([string $code]). It usually is
 * not needed to pass on a code: Lists will assume it is the page's code,
 * which is, or should be, the case for most lists. However, if you need
 * to present a list in a page where pageCode and objCode differ, you'll
 * need to pass the code as a parameter (alternatively, you can call
 * Lists#setCode() to set it beforehand).
 *
 * Lists#printList() will attempt to load static data in the first phase,
 * which includes the list of fields (for the tabular data), a few params
 * like screen name (singular and plural) and the list of tools that will
 * be available to a tabular list: edit, delete, block, are the most
 * common options.
 *
 * Creating a new Data Provider script, or editing an existing one:
 *
 * You don't need to worry about HOW data arrives to where it will be used.
 * All you need to do is create a regular PHP script in staticData/ folder
 * and define there the right variables, as follows:
 *
 * $fields
 	A hash containing field name => screen name, where field name has to
	match that set in the database and screen name is the name of that
	column. Only columns present in this list (as keys) will be printed.
 *
 * $actions
 	This is a list of possible actions. The list is a set of key => value
	pairs, where key is the action's code (or tool code) and value is the
	screen name for this action (shown in button's title, alt, and used
	to load the picture for it). By default it contains the most commonly
	used actions (edit, delete, block) and you shouldn't need to edit it
	or add more entries in regular lists. If you do, remember you will
	need to save a picture for it too, in '{$IMAGES_PATH}/buttons/{$code}.png'
 *
 * $tools							i.e. array('edit', 'delete')
 *	A list of tools to show in each row. These tools need to be defined in
 *	$actions. Edit and delete are given by default (redifining $tools would
 *	overwrite it though). In any case, presenting them in the list will
 *	still depend on the user having enough rights to use those actions.
 *
 * On a second phase (through ajax as soon as the 'frame' is loaded), the
 * JS part of this library will request the content of the list. This is
 * where dynamic data retrieved from the database is required. Most of it
 * is automatized, so there's really not much to do to 'make it work'. It
 * just does, as soon as the queries are defined.
 * See SQL_Lists for more info on this.
 */



	class Lists extends SQL_Lists{

		private $src=NULL;				# Alternative source

		private $combo=true;			# Whether to include comboList in tabular lists

		private $comboOptions=array();	# Lists of options for simpleLists (create/edit)



/***************
** HARDCODED LISTS
***************/

		public function salesTypes(){

			return array(
				'install'	=> 'Instalación',
				'sale'		=> 'Venta',
				'service'	=> 'Visita Técnica',
			);

		}

		public function warranties(){	/* In months */
			return array(
				12	=> '1 año',
				6	=> '6 meses',
				0	=> '(sin garantía)',
			);
		}



/***************
** OBJECT'S MAIN (TABULAR) LISTS
***************/

		public function printList($code=NULL, $modifier=NULL)
		{
			# Update page content (prints list frame)
			Response::content($this->listHTML($code, $modifier), true);

			# Call JS function to start rolling
			return $this->initializeList();
		}

		public function initializeList()
		{
			return addScript("initializeList('{$this->code}', '{$this->modifier}', '{$this->src}');");
		}

		/**
		 * This function generates a list of any particular object suitable for
		 * tabular listing (users, customers, products, etc.). It doesn't fill
		 * it with the actual data yet.
		 */
		public function listHTML($code=NULL, $modifier=NULL)
		{
			$this->code = is_null($code) ? PageController::getParams('info')['id'] : $code;
			$this->modifier = $modifier;

			# Import static data
			$static = $this->getStaticData();

			# Put it in Smarty's universe
			Template::one()->assign('code', $this->code);
			Template::one()->assign('modifier', $this->modifier);
			Template::one()->assign('fields', $static['fields']);
			Template::one()->assign('params', $static['params']);
			Template::one()->assign('comboList', NULL);	# Initialize

			# Combo list ($code, $modifier)
			if( $this->combo ) $this->includeComboList($this->code, $this->modifier);

			# Fetch the template and return it
			return $this->template('listFrame');
		}


/***************
** OBJECT'S SIMPLE LISTS
***************/

		public function printSimpleList($code=NULL, $modifier=NULL){

			# Update page content (prints simpleList)
			Response::content($this->simpleListHTML($code, $modifier), true);

			# Call JS function to start rolling
			return $this->initializeSimpleList();

		}

		public function initializeSimpleList(){
			return addScript( "initializeSimpleList();" );
		}

		public function simpleListHTML($code=NULL, $modifier=NULL, $filters=array()){

			$this->code = is_null($code) ? PageController::getParams('info')['id'] : $code;
			$this->modifier = $modifier;

			# Import static data
			$static = $this->getStaticData();
			$tipField = $static['params']['tipField'];

			# Pre-process filters if function name was defined (in static object's data)
			if( $static['preProcess'] && function_exists($static['preProcess']) ){
				call_user_func_array($static['preProcess'], array(&$filter));
			}

			# Get Data
			$src = ($this->src ? $this->src : $this->code).'SL';
			$data = $this->$src($filters, $modifier);

			# Post-process data if post-process function was defined in static object's data
			if( $static['postProcess'] && function_exists($static['postProcess']) ){
				call_user_func_array($static['postProcess'], array(&$data));
			}

			# Sort defined fields as defined (w/ user-defined function array_sort_keys)
			foreach ($data as $k => &$v)
			{
				array_sort_keys($data[$k], $static['fields']);

				$v['tip'] = isset($v[$tipField]) ? $v[$tipField] : '';
				$v['tools'] = $static['tools'];
			}

			# Put it in Smarty's universe
			Template::one()->assign('simpleListID', $static['id']);
			Template::one()->assign('code', $this->code);
			Template::one()->assign('modifier', $this->modifier);
			Template::one()->assign('fields', $static['fields']);
			Template::one()->assign('noInput', $static['noInput']);
			Template::one()->assign('params', $static['params']);
			Template::one()->assign('tools', $static['tools']);
			Template::one()->assign('axns', $static['actions']);
			Template::one()->assign('data', isset($data) ? $data : array());
			Template::one()->assign('canCreate', Access::can('create' . ucfirst($this->code)) );

			Template::one()->assign('comboOptions', $this->comboOptions);

			# Fetch the template and return it
			return $this->template('simpleList');

		}



/***************
** HASHED (COMBO) LISTS
***************/

		public function comboListHTML($code, $modifier=NULL, $selected=''){

			if( is_null($code) ) $code = PageController::getParams('info')['id'];
			$params = array('name' => NULL);

			# Attempt to load config file
			if( is_file($path=STATIC_DATA_PATH."{$code}.sd.php") ) require( $path );

			Template::one()->assign('combo', array(
				'code'		=> $code,
				'params'	=> array('name' => $params['name']),
				'list'		=> $this->$code( $modifier ),
				'selected'	=> $selected,
			));

			return $this->template( 'comboList' );

		}

		public function includeComboList($code=NULL, $modifier=NULL, $sel=''){

			Template::one()->assign('comboList', $this->comboListHTML($code, $modifier, $sel));

		}



/***************
** PRIVATE METHODS
***************/

		private function getStaticData( $key=NULL ){

			# Let required (SD) file know if we got a modifier
			$code = $this->code;
			$modifier = $this->modifier;

			# Initialize required keys
			$id = NULL;
			$params = array();
			$fields = array();
			$tools = array('edit', 'delete');
			$actions = array(
				'edit'		=> 'editar',
				'delete'	=> 'borrar',
				'block'		=> 'bloquear',
			);
			$noInput = array();
			$preProcess = NULL;
			$postProcess = NULL;

			# Attempt to load config file
			if( is_file($path=STATIC_DATA_PATH."{$code}.sd.php") ) require( $path );

			# Fix/Complete Params
			$params += array(
				'tipField'	=> NULL,
				'code'		=> $this->code,
				'name'		=> '',
				'plural'	=> ''
			);

			$axns = array();
			# Fix/Complete Tools
			foreach( array_reverse($tools) as $axn ) $axns[$axn] = $axn.ucfirst($code);
			# Fix/Complete Fields
			foreach( $fields as $k => $field ){
				if( is_null($field) ) unset( $fields[$k] );
				if( !isset($noInput[$k]) ) $noInput[$k] = NULL;
				if( is_array($field) && !isset($noInput[$field[1]]) ) $noInput[$field[1]] = NULL;
			}

			# Return a hash with all relevant static data for current list
			return $key
				? (isset($$key) ? $$key : NULL)
				: array(
					'id'			=> $id,
					'params'		=> $params,
					'fields'		=> $fields,
					'noInput'		=> $noInput,
					'tools'			=> isset($axns) ? $axns : array(),
					'actions'		=> $actions,
					'preProcess'	=> $preProcess,
					'postProcess'	=> $postProcess,
				);

		}

		private function template( $which ){

			return is_file($path=dirname(__FILE__)."/templates/{$which}.tpl")
				? Template::one()->fetch( $path )
				: '';

		}

		public function hasCombo( $bool=true ){

			$this->combo = $bool;

		}

		/**
		 * Sets an altenative source for data. The code passed as paramter follows
		 * the same rules as a real code (meaning lists based on it will be appended
		 * like users => usersList, usersSP, users, etc, when requesting the data
		 * from the DB.
		 */
		public function setSource( $src ){

			$this->src = $src;

		}

		public function addComboOptions($name, $options=array()){

			$this->comboOptions[$name] = $options;

		}

	}