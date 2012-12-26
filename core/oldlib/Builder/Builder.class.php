<?php

return ($Builder = new Builder());



/* Shortcuts to global objects */
function getBuilderObject($obj, $code=NULL){

	return $GLOBALS['Builder']->get($obj, $code);

}

function oStats( $code=NULL )		{	return getBuilderObject('Stats', $code);		}
function oFormTable( $code=NULL )	{	return getBuilderObject('FormTable', $code);	}

function oSQL()						{	return getBuilderObject( 'SQL' );				}
function oNav()						{	return getBuilderObject( 'Nav' );				}
function oPageCfg()					{	return getBuilderObject( 'PageCfg' );			}

function oTabs()					{	return getBuilderObject( 'Tabs' );				}

function oAlerts()					{	return getBuilderObject( 'Alerts' );			}
function oValidate()				{	return getBuilderObject( 'Validate' );			}



class Builder{

	private $objects;

	/**
	 * Constructor: initialize private member objects
	 */
	public function __construct(){

		$this->objects = array();

	}

	/**
	 * Get is the only public method of this class. It returns the stored object if found
	 * or creates one, stores it for later calls, and returns it.
	 */
	public function get($obj, $code=NULL, $forceNew=false)
	{
		return !$forceNew && isset($this->objects[$obj])
		  && (is_null($code) || $this->objects[$obj]['code'] === $code)
			? $this->objects[$obj]['object']
			: $this->build($obj, $code);
	}


	/**
	 * Builds an object given its name, if it's predefined in Builder class. Path to the class
	 * definition is expected to be well-formatted, {$name}/{$name}.class.php, in CLASSES_PATH.
	 * If the object class is not defined in Builder, or for some reason the class definition
	 * cannot be loaded, an error will be triggered and NULL returned.
	 */
	private function build($obj, $code){

		# Load all classes this class depends on (if any)
		$this->loadDependencies( $obj );

		$classPath = $this->classPath( $obj );

		# Make sure the file with the class definition is reachable or abort
		if( !is_file($classPath) ){
			trigger_error("BUILDER ERROR: Object {$obj} is not registered or its path is incorrect ({$classPath})");
			return NULL;
		}

		# Include class definition file
		require_once( $classPath );

		# Initiate the object of class $obj
		switch( $obj ){
			case 'Alerts':
				$Alerts = new Alerts(getSes('user'));
				break;

			case 'FormTable':
				$FormTable = new FormTable;
				break;

			case 'Nav':
				$Nav = new Nav;
				break;

			case 'PageCfg':
				$PageCfg = new PageCfg;
				break;

			case 'SQL':
				$SQL = new SQL;
				break;

			case 'Stats':
				$Stats = new Stats;
				break;

			case 'Tabs':
				$Tabs = new Tabs;
				break;

			case 'Validate':
				$Validate = new Validate;
				break;

			default:
				${$obj} = NULL;			/* Class is not expected by Builder */
				trigger_error("BUILDER ERROR: Object {$obj} is not registered in this class");
				break;

		}

		# Store newly created object in local list of objects for later retrieval
		$this->objects[$obj] = array('object' => ${$obj}, 'code' => $code);

		return $this->objects[$obj]['object'];

	}

	/**
	 * Some classes might make it hard to keep the right path structure, specially
	 * if it's a secondary class of a big library.
	 * Just add those exceptions to the list, in the switch, returning the right
	 * path as a string (from base directory, above app/).
	 */
	private function classPath( $obj ){

		switch( $obj ){
			case 'fPDF':
				return THIRD_PARTY_PATH . '/fPDF/ExtendedFPDF.class.php';
			break;
			case 'FormTable':
			case 'PageCfg':
			case 'SQL':
			case 'Validate':
				return CLASSES_PATH . "/{$obj}/{$obj}.class.php";
			break;
			default:
				return CORE_LIB . "/{$obj}/{$obj}.class.php";
			break;
		}

	}

	/**
	 * If a class requires another class to be instantiated (or at least loaded)
	 * add an entry to the switch under that class, and assign dependencies to
	 * $list var (always an array, even if it's a single element array).
	 */
	private function loadDependencies( $obj )
	{
		if (isset($list) && is_array($list))
		{
			foreach ($list as $item)
			{
				$this->get($item);
			}
		}

	}

}