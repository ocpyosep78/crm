<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	class PDF_Estimates_Provider extends PDF_Estimates_SQL{

		private $drawer;		/* Link to drawer object */

		private $atts;			/* Collection of attributes derived from estimate's params */

		public $estimate;
		public $customer;
		public $system;
		public $products;
		public $plan;

		/**
		 * Most Data Provider methods depend on the system for which we're
		 * building the estimate's PDF. Thus a system code must be given at
		 * construct. Possible values are CCTV, Domotics, Alarms, Fire, etc.
		 */
		public function __construct($id, $drawer){

			parent::__construct();

			$this->drawer = $drawer;	/* Link to PDF-drawer object */

			$this->estimate = !empty($id)
				? $this->getEstimateData( $id )
				: NULL;

			$this->customer = !empty($this->estimate['info']['id_customer'])
				? $this->getCustomer( $this->estimate['info']['id_customer'] )
				: NULL;

			$this->system = !empty($this->estimate['info']['id_system'])
				? $this->getSystem( $this->estimate['info']['id_system'] )
				: NULL;

			$this->products = !empty($this->estimate['items']['products'])
				? $this->estimate['items']['products']
				: NULL;

			$this->plan = !empty($this->estimate['info']['id_estimate'])
				? $this->getPlan( $this->estimate['info']['id_estimate'] )
				: NULL;

			$this->setAtts();

		}

		private function setAtts(){

			# Work with products when you need them by category, with $this->products when by ID
			$products = array('dvr' => array(), 'gv' => array(), 'geo' => array());
			foreach( $this->products as $p ) $products[$p['id_category']][$p['id_product']] = $p;

			# Get information about graphic-related products included (DVR, GeoVision, GV)
			$DVR = !empty($products['dvr']) ? array_shift($aux=$products['dvr']) : NULL;
			$GV = !empty($products['gv']) ? array_shift($aux=$products['gv']) : NULL;
			$Geo = !empty($products['geo']) ? array_shift($aux=$products['geo']) : NULL;

			$channels = array(
				'dvr'	=> !empty($DVR) && preg_match('/(\d+) *$/', $DVR['code'], $aux) ? array_pop($aux) : 0,
				'gv'	=> !empty($GV) && preg_match('/(\d+) *$/', $GV['code'], $aux) ? array_pop($aux) : 0,
				'geo'	=> !empty($Geo) && preg_match('/(\d+) *$/', $Geo['code'], $aux) ? array_pop($aux) : 0,
			);

			# Cameras count (all kinds together)
			$cams = 0;
			if( isset($products['cameras']) ) foreach( $products['cameras'] as $cam ) $cams += $cam['amount'];

			$atts = array(
				# products by category and by ID
				'productsXcategory'	=> $products,
				'productsXid'		=> $this->products,
				'graphicChannels'	=> $channels,
				'graphicalHW'		=> $products['dvr'] + $products['gv'] + $products['geo'],
				# Amount of cameras
				'cams'				=> $cams,
				# Main estimate IDs (estimate, customer, system)
				'estimate'			=> $this->estimate ? $this->estimate['info']['id_estimate'] : NULL,
				'customer'			=> $this->customer ? $this->customer['id_customer'] : NULL,
				'system'			=> $this->system ? $this->system['code'] : NULL,
				# Configuration by GET (downloadable, printable)
				'downloadable'		=> isset($_GET['download']),
				'printable'			=> $atts['downloadable'] ? false : isset($_GET['printer']),
				# Shortcuts (most act like both boolean or integer, as it fits best)
				'isDVR'				=> $channels['dvr'],
				'isGV'				=> $channels['gv'],
				'isGeo'				=> $channels['geo'],
				'hasServer'			=> !empty($products['servers']) ? array_shift($aux=$products['servers']) : false,
				'hasScreen'			=> !empty($products['screens']) ? array_shift($aux=$products['screens']) : false,
				'hasPlan'			=> !empty($this->plan) ? $this->plan : false,
			);

			return $this->atts = $atts;

		}

		public function getAtts(){

			return $this->atts;

		}

		private function get( $att ){

			return isset($this->atts[$att]) ? $this->atts[$att] : NULL;

		}

		private function warn( $msg ){

			return $this->drawer->warn( $msg );

		}


/***************
** E X T E R N A L   S O U R C E S
***************/

		/**
		 * Retrieves all information of an estimate from the DB and returns
		 * it as an array('info' => $info, 'items' => $items). The results
		 * are cached for next calls, in private var $estimate.
		 */
		public function getEstimateData( $id=NULL ){

			if( is_null($id) ) $id = $this->estimate;
			if( !is_null($this->estimate) ) return $this->estimate;

			$info = $this->SQL_getEstimateData( $id )
				or die('No se encontró el presupuesto buscado.');

			$detail = $this->SQL_getEstimateDetail( $id )
				or die('El presupuesto pedido está vacío o no se pudo acceder a su contenido.');
			foreach( $detail as $k => $row ) $items[$row['type']][$k] = $row;

			return $this->estimate = array('info' => $info, 'items' => $items);

		}

		/**
		 * Function getCustomer returns all info that might be needed about the
		 * customer we're estimating for. This is basically raw info taken from DB.
		 * The results are cached for next calls in private var $customer.
		 */
		public function getCustomer( $id=NULL ){

			if( is_null($id) ) $id = $this->cust;
			if( !is_null($this->customer) ) return $this->customer;

			$info = $this->SQL_getCustomer( $id );
			$info['contact'] = is_null($info['contact']) ? '' : "Att. Sr(es) {$info['contact']}";

			return $this->customer = $info;

		}

		/**
		 * Function getSystem returns all info that might be needed about the
		 * system the estimate belongs to. This is basically raw info taken from DB.
		 * The results are cached for next calls in private var $system.
		 */
		public function getSystem( $id=NULL ){

			if( is_null($id) ) $id = $this->get('system');
			if( !is_null($this->system) ) return $this->system;

			$info = $this->SQL_getSystem( $id );

			return $this->system = $info;

		}

		public function getPlan( $id=NULL ){

			if( is_null($id) ) $id = $this->get('estimate');
			if( !is_null($this->plan) ) return $this->plan;

			return $this->plan = $this->getInstallPlan( $id );

		}

		/**
		 * An estimate's PDF has pictures related to the customer we're estimating for.
		 * These pictures could be taken from customer's images folder or elsewhere. For
		 * example, one could choose to attach pictures found online. This method returns
		 * the path (local or http) to a given picture (for each customer we expect 3
		 * pictures: small, mid and large).
		 */
		public function getCustImgs()
		{
			$generic = IMG_PATH . "/customers/{$this->cust}%s.jpg";
			$unknown = IMG_PATH . '/customers/unknown.gif';

			$paths['small'] = is_file($path=sprintf($generic, '')) ? $path : $unknown;
			$paths['mid'] = is_file($path=sprintf($generic, 'mid')) ? $path : $paths['small'];
			$paths['large'] = is_file($path=sprintf($generic, 'large')) ? $path : $paths['mid'];

			return $paths;
		}


/***************
** S T R U C T U R E
***************/

		/**
		 * Each page is identified by a code, that points to a given page list.
		 * Given a system type, it returns the corresponding list, by page code.
		 * For example, intro is a common page that describes what kind of materials
		 * could be used, what tasks are required for an install, times, etc.
		 * However, CCTV is a page describing this type of system, and it belongs
		 * only to this system's structure.
		 */
		public function getPDFPages(){

			# Build whole set of pages, then mark those that should be removed
			$cctv = array(
				'Intro',
				'CCTV',
				'Plan',
				'Server',
				'Screen',
				'Totals',
				'About',
			);
			$toRemove['cctv']['Server'] = !$this->get('hasServer');
			$toRemove['cctv']['Screen'] = !$this->get('hasScreen');
			$toRemove['cctv']['Plan'] = !$this->get('hasPlan');

			# Remove all items marked for removal
			if( isset($toRemove) ) foreach( $toRemove as $type => $item ){
				if( isset(${$type}) ) foreach( $item as $k => $v ){
					if( $v ) unset( ${$type}[array_shift(array_keys(${$type}, $k))] );
				}
			}

			return ${$this->get('system')} ? ${$this->get('system')} : array();

		}

		/**
		 * Returns the list, titles and content from each paragraph to appear in
		 * Intro page (which might depend on the chosen system).
		*/
		public function getParagraphs( $data ){

			foreach( (array)$data as $code ){
				if( !is_null($pgph=$this->paragraphs($code)) ){
					$pgphs[$this->paragraph_code2title($code)] = "    ".$pgph;
				}
			}

			return isset($pgphs) ? $pgphs : array();

		}


/***************
** C O M M O N   P A G E S :   I N T R O
***************/

		/**
		 * Returns a list of paragraphs that belong to Intro page, depending on given
		 * system (paragraphs codes match those defined in self::paragraphs()
		 */
		public function introParagraphs(){

			switch( strtolower($this->get('system')) ){
				# Controles de Acceso
				case 'access': return array();
				# Sistema de Alarmas
				case 'alarms': return array();
				# Cableado Estructurado
				case 'cabling': return array();
				# Sistema CCTV
				case 'cctv': return array('description', 'materials', 'tasks', 'times');
				# Domótica
				case 'domotics': return array();
				# Detección de Incendios
				case 'fire': return array();
				# Centrales Telefónicas
				case 'central': return array();
				# Unknown system (error)
				default: return array();
			}

		}


/***************
** C O M M O N   P A G E S :   A B O U T
***************/

		/**
		 * Returns a list of paragraphs that belong to About page, depending on given
		 * system (paragraphs codes match those defined in self::paragraphs()
		 */
		public function aboutParagraphs(){

			switch( strtolower($this->get('system')) ){
				# Controles de Acceso
				case 'access': return array();
				# Sistema de Alarmas
				case 'alarms': return array();
				# Cableado Estructurado
				case 'cabling': return array();
				# Sistema CCTV
				case 'cctv': return array('company', 'quality');
				# Domótica
				case 'domotics': return array();
				# Detección de Incendios
				case 'fire': return array();
				# Centrales Telefónicas
				case 'central': return array();
				# Unknown system (error)
				default: return array();
			}

		}


/***************
** C O M M O N   T O O L S :   P A R A G R A P H S
***************/

		public function paragraph_code2title( $code ){

			$hash = array(
				'description'	=> 'Descripción del sistema',
				'materials'		=> 'Materiales de instalación',
				'tasks'			=> 'Tareas a realizar',
				'times'			=> 'Tiempo de instalación',
				'cctvInclude'	=> 'Incluye:',
				'changesNote'	=> 'IMPORTANTE',
				'server'		=> 'Servidor con tecnología Intel',
				'screen'		=> 'Monitor ViewSonic 19"',
				'company'		=> 'Nuestra empresa',
				'quality'		=> 'Calidad, Seriedad y Confianza',
			);

			return isset($hash[$code]) ? $hash[$code] : NULL;

		}

		/**
		 * This is a simple list of all regular paragraphs that might appear in an
		 * estimate, returned by their respective codes. Though they're basically
		 * attached to shared page Intro, there might be paragraphs in other pages.
		 */
		public function paragraphs( $pgph ){

			switch( $pgph ){

				case 'description':	$desc = $this->get('isDVR')
										? 'un equipo de video DVR x '.$this->get('isDVR').' canales.'
										: ($this->get('isGV')
											? 'una placa GV x '.$this->get('isGV').' canales, instalada en el servidor.'
											: ($this->get('isGeo')
												? 'una placa GeoVision x '.$this->get('isGeo').', instalada en el servidor.'
												: NULL));
									return !$desc ? NULL : sprintf($this->readParagraph($pgph), $desc);

				case 'tasks':		return ($this->get('hasServer') ? 'Se armarán los servidores y se' : 'Se').
										substr($this->readParagraph($pgph), 2);

				case 'times':		return sprintf($this->readParagraph( $pgph ), ($this->get('cams') <= 8
										? 'De 1 a 3 días'
										: ($this->get('cams') <= 15 ? 'De 2 a 5 días' : 'A confirmar')));


				default: return $this->readParagraph( $pgph );

			}

		}

		private function readParagraph( $pgph ){

			return is_file($path=dirname(__FILE__)."/paragraphs/{$pgph}.txt")
				? preg_replace('/<!--(.*)-->/Uis', '', file_get_contents($path))
				: NULL;

		}

	}