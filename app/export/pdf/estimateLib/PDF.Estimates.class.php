<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	class PDF_Estimates extends ExtendedFPDF{
	
		protected $id;					# Estimate ID
		protected $DP;
		protected $atts;				# Collection of attributes derived from estimate's params
		
		private $warnings=array();		# List of warnings if raised
		
		/**
		 * Initialize general config vars and display options
		 */
		public function __construct( $id ){
		
			parent::__construct('P', 'mm');
		
			$this->AliasNbPages();
			$this->SetFont('Arial', '', 12);
			$this->SetPadding(0);
			$this->SetAutoPageBreak(true, 30);
			
			$this->id = $id;
			
			# Data Provider
			$this->DP = new PDF_Estimates_Provider($id, $this);
			
			# Find out which kind of estimate we're building
			$this->atts = $this->DP->getAtts();
			
		}
		
		/**
		 * With all the info from the Provider, we're ready to validate the estimate, see if
		 * anything's missing (or in excess) and warn the user before presenting the PDF. The
		 * exportation can go on with mild warnings (severe errors will quit the execution,
		 * though).
		 * 
		 * When the parameter $summary is set to true, a summary report is printed instead of
		 * the PDF, with or without warnings raised (this is usually the first step, and the
		 * PDF is shown if the user confirms everything's in place).
		 */
		public function validate( ){
			
			if( isset($_GET['validated']) ) return;
		
			$atts = $this->atts;
			$DP = $this->DP;
			
			# FATAL ERRORS
			if( empty($DP->estimate) ){
				die('No se encontró el presupuesto pedido en la base de datos. Revise sus datos e inténtelo nuevamente.');
			}
			if( empty($DP->customer) ){
				die('El presupuesto no está asociado a ningún cliente o el cliente no fue encontrado en la base de datos.');
			}
			if( empty($DP->system) ){
				die('El presupuesto no está asociado a un sistema, o el sistema asociado no fue encontrado en la base de datos.');
			}
			if( empty($DP->system) ){
				die('La lista de productos del presupuesto está vacía, no se puede continuar.');
			}
			
			# WARNINGS
			if( empty($DP->plan) ){
				$this->warn('<span>No se ha definido un plan de obra</span> para este presupuesto.<br />'.
					'Si continúa, no se incluirá la hoja correspondiente a la planificación de obra.');
			}
			if( $atts['system'] == 'cctv' ){
				if( !!$atts['isDVR'] + !!$atts['isGV'] + !!$atts['isGeo'] > 1 ){
					$this->warn('El sistema posee <span>2 o más tipos de procesadores de gráficos</span> (GV, DVR, GeoVision)<br />'.
						'No se recomienda continuar sin corregir este problema.');
				}
				if( count($atts['graphicalHW']) > 1 ){
					$this->warn('El sistema posee <span>2 o más productos de tipo '.
						($atts['isDVR'] ? 'DVR' : ($atts['isGV'] ? 'GV' : ($atts['isGeo'] ? 'GeoVision' : '(desconocido)'))).
						'</span>. Si decide ignorar esta advertencia, es posible que la descripción del sistema no sea la adecuada.');
				}
				if( !$atts['isDVR'] && !$atts['isGV'] && !$atts['isGeo'] ){
					$this->warn('El sistema CCTV <span>no posee placas (GV, Geo) ni DVR</span> para el procesamiento gráfico.<br />'.
						'No se recomienda continuar sin corregir este problema.');
				}
				if( !$atts['hasServer'] && ($atts['isGV'] || $atts['isGeo']) ){
					$this->warn('El sistema CCTV utiliza placas Geo o GV pero <span>no incluye servidor</span>.<br />'.
						'Puede continuar sin inconvenientes si desea presupuestar sin servidor.');
				}
				if( !$atts['hasScreen'] ){
					$this->warn('El sistema CCTV <span>no incluye monitor</span>.<br />'.
						'Puede continuar sin inconvenientes si desea presupuestar sin monitor.');
				}
			}
			
			$this->showWarnings();
			
		}
		
		public function warn( $msg ){ $this->warnings[] = $msg; }
		public function showWarnings(){
			
			if( isset($_GET['validated']) ) return;
			
			if( empty($this->warnings) ) header("Location: {$_SERVER['REQUEST_URI']}&validated");
			
			oSmarty()->assign('id', $this->id);
			oSmarty()->assign('_GET', $_GET);
			oSmarty()->assign('warnings', $this->warnings);
			
			oSmarty()->display('estimates/estimatePDFwarnings.tpl');
			
			exit();
			
		}
		
		/**
		 * Get estimate's info from Data Provider
		 * Passing either data or items as parameter will return that particular
		 * key in the array (estimate info is divided into estimate's base info
		 * and a list of items with their properties -code, amount, price, etc).
		 */
		 public function data( $what=NULL ){
		 	$data = $this->DP->getEstimateData();
		 	return $what ? (isset($what) ? $data[$what] : NULL) : $data;
		 }
		
		/**
		 * Get customer's info from Data Provider
		 */
		 public function cust( $what=NULL ){
		 	$data = $this->DP->getCustomer();
		 	return $what ? (isset($what) ? $data[$what] : NULL) : $data;
		 }
		
		/**
		 * Get system's info from Data Provider
		 */
		 public function sys( $what=NULL ){
		 	$data = $this->DP->getSystem();
		 	return $what ? (isset($what) ? $data[$what] : NULL) : $data;
		 }
		
		
		
		/**
		 * Display is this clases' main function. Actualy, the only one you should
		 * call except for configuring methods like self::forPrinting() and
		 * self::forDownload().
		 * 
		 * It takes all automatic information from the Data Provided (passed as only
		 * argument) and calls each page's main method. Then it's up to them to do
		 * complete their tasks.
		 * 
		 * For easy developing, you can list non-created pages in DP's page list. Just
		 * remember it won't display errors (shouldn't you know it doesn't exist?).
		 */
		public function display(){
		
			# Get list of pages, and call them (if coded)
			foreach( $this->DP->getPDFPages() as $page ){
				if( method_exists($this, $method="draw{$page}") ) $this->$method();
			}
			
			# Print resulting PDF
			$this->Output('presupuesto.pdf', $this->atts['downloadable'] ? 'D' : 'I');
			
		}
		
		
/***************
** HEADER / FOOTER
***************/
	
		public function Header(){
		
			# Page background
			if( !$this->atts['printable'] ){
				$this->BackgroundImage('app/images/export/bgEstimates.jpg', true);
			}
			
			# Page Title
			$this->SetFont('Arial', 'B', 14);
			$this->SetXY(55, 15);
			$this->SetFillColor(0, 150, 200);
			$this->MultiCell(76, NULL, strtoupper($this->headerTitle), NULL, 'C');
			# Title underline
			$this->Cell(44, 1, '');
			$this->Cell(78, 1, '', 0, 1, NULL, 1);
			
			# Move cursor to next position
			$this->SetY(40);
			
			# Show date to the right, rotated 90º
			$this->SetFont('Arial', '', 12);
			if( $this->atts['printable'] ){
				$this->SetXY(161, 29);
				$this->Write(8, 'Fecha: ');
				$this->SetFont('Arial', 'B', 11);
				$this->Write(8, date('d/m/Y'));
			}
			else{
				$this->RotatedText($this->w - 4, 34, 'Fecha ', -90);
				$this->SetFont('Arial', 'B', 11);
				$this->RotatedText($this->w - 4, 48, date('d/m/Y'), -90);
			}
			
			$this->SetXY($this->lMargin, 40);
			
		}
		
		//Pie de página
		public function Footer(){
return;	/* Disabled for now */
			//Posición: a 1,5 cm del final
			$this->SetY(-15);
			//Arial italic 8
			$this->SetFont('Arial', 'I', 8);
			//Número de página
			$this->Cell(0, 10, 'Page '.$this->PageNo().'/{nb}', 0, 0, 'C');
			
		}
		
		
/***************
** PARAGRAPHS
***************/
		protected function printParagraph($title, $text, $titleSize=14, $textSize=11){
		
			$this->SubTitle($title, $titleSize);
			
			$this->SetFontSize( $textSize );
			$this->MultiCell(NULL, 5, $text);
			
		}
		
		protected function printParagraphs($pgphs=array(), $titleSize=14, $textSize=11){
		
			foreach( $pgphs as $title => $text ){
				$this->printParagraph($title, $text, $titleSize, $textSize);
			}
			
		}
		
		protected function SubTitle($txt='', $size=NULL){
		
			$currentSize = $this->FontSizePt;
			
			# Leave a space above the subtitle
			$this->Cell(NULL, 8, '', NULL, 2);
		
			# Store current font config
			$color = $this->TextColor;
			$this->SetFontSize( !is_null($size) ? $size : $this->FontSizePt + 1 );
			
			$this->SetTextColor(255, 100, 51);
			$this->Cell(NULL, NULL, $txt, NULL, 1, 'L');
			
			# Restore font config
			$this->TextColor = $color;
			$this->SetFontSize = $currentSize;
			
			# Leave a space below the subtitle
			$this->Cell(NULL, 2, '', NULL, 2);
		
		}
		
	}

?>