<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	require_once( CLASSES_PATH.'fPDF/ExtendedFPDF.class.php' );
	require_once( dirname(__FILE__).'/PDF.TechVisits.Provider.class.php' );
	
	class PDF_TechVisits extends ExtendedFPDF{
	
		protected $DP;
		private $printable=false;		# Printable doesn't draw background image
		private $downloadable=false;	# Downloadable forces download in browser
		
		public function __construct( $id ){
		
			parent::__construct('P', 'mm');
		
			$this->AliasNbPages();
			$this->SetMargins(5, 5);
			$this->SetPadding(0);
			$this->SetFont('Arial', 'B', 12);
	
			# Data Provider
			$this->DP = new PDF_TechVisits_Provider( $id );
			
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
		public function Display(){
 
			# Add page with header and footer
			$this->AddPage('P', $this->printable ? 'A4' : array(210, 202));
		
			# Page background
			if( !$this->printable ){
				$this->Image('app/images/background/techVisits.jpg', -7.5, -3, 223, 213);
			}
			
			$this->DrawData();
			
			# Print resulting PDF
			$this->Output('Constancia Técnica.pdf', $this->downloadable ? 'D' : 'I');
			
		}
		
		/**
		 * When set to true, self::printable tells Header not to print background image
		 */
		public function forPrinting( $bool=true ){
			$this->printable = $bool;
		}
	
		
		/**
		 * When set to true, browser is asked to force download of the PDF file
		 * NOTE: when set for download, self::printable is ignored (set to false, that is)
		 */
		public function forDownload( $bool=true ){
			$this->downloadable = $bool;
			if( $bool == true ) $this->printable = false;
		}
		
		
/***************
** DRAWING PAGE
***************/

		private function drawData(){
		
			# Get usefull data packs
			$v = $this->DP->getData();
			$cust = $this->limitLength($this->DP->getCustomerInfo(), 83);
			$cost = $this->DP->getRelatedInvoice();
			$period = $this->DP->getPeriod();
		
			if( isset($_GET['rules']) ) $this->rules();		/* TESTING */
			
			# Extra information
			if( !$this->printable ){
				$this->SetTextColor(30, 0, 120);
				$this->SetXY(182, 0);
				$this->SetFontSize(16);
				$this->Cell(NULL, NULL, $v['number']);
				$this->SetFontSize(10);
				$this->SetXY(171, 8);
				if( !empty($v['technicianName']) ){
					$this->Cell(30, 5, "Técnico responsable", 0, 2, 'C');
					$this->Cell(30, NULL, $v['technicianName'], 0, 1, 'C');
				}
				$this->SetTextColor( 0 );
			}
			
			$this->SetFontSize(12);
		
			# VISIT DATE
			$w = 12;
			list($y, $m, $d) = explode('-', $v['date']);
			$this->SetXY(178, 27.5);
			$this->Cell($w, NULL, $d);
			$this->Cell($w, NULL, $m);
			$this->Cell(NULL, NULL, substr($y, 2));
			
			# CUSTOMER DATA (LEFT)
			$h = 8.65;
			$this->SetXY(47, 37.8);
			$this->Cell(NULL, $h, $cust['name'], 0, 2);
			$this->Cell(NULL, $h, $cust['contact'], 0, 2);
			$this->Cell(NULL, $h, $cust['address'], 0, 2);
			$this->Cell(NULL, $h, $cust['phone'], 0, 2);
			
			# CUSTOMER/SALE DATA (RIGHT)
			$this->SetXY(171, 40);										# Number
			$this->Cell(NULL, NULL, $cust['number'], 0, 2);
			$x = array(199.4, 181);
			$this->SetXY($x[(int)$cust['subscribed']], 49.5);			# Subscription
			$this->Cell(NULL, NULL, 'X');
			$this->SetXY($x[(int)$cust['warranty']], 58);				# Warranty
			$this->Cell(NULL, NULL, 'X');
			$this->SetXY(173.5, 65.8);									# Install date
			$w = 13.6;
			$this->Cell($w, NULL, $cust['installDate']['d']);
			$this->Cell($w, NULL, $cust['installDate']['m']);
			$this->Cell(NULL, NULL, $cust['installDate']['y']);
			
			# BODY (upper)
			$body = $this->limitLength($this->DP->getBodyInfo(), 127);
			$h = 10.66;
			$this->SetXY(80, 75);										# System, Reason, Outcome
			$this->Cell(NULL, $h, $body['system'], 0, 2);
			$this->Cell(NULL, $h, $body['reason'], 0, 2);
			$this->Cell(NULL, $h, $body['outcome'], 0, 2);
			$this->SetX( $body['complete'] ? 84.9 : 101 );				# Complete/Incomplete
			$this->Cell(1, 1, '', 0, 2);
			$this->Cell(NULL, $h, is_null($body['complete']) ? '' : 'X');
			$this->SetX( 128 );
			$this->Cell(NULL, $h, $this->limitLength($body['excuse'], 77), 0, 1);
			$this->SetX( 78.5 );
			$this->Cell(NULL, $h, $body['used'], 0, 1);
			# BODY (lower)
			$this->SetFontSize( 14 );
			$h = 10.66;
			$this->SetXY(85, 128.5);									# Cost ($ and US$)
			$this->Cell(NULL, $h, $cost['cost']);
			$this->SetX( 121.5 );
			$this->Cell(NULL, $h, $cost['costDollars']);
			$this->SetX( 186 );											# Invoice
			$this->Cell(NULL, $h, $cost['invoice'], 0, 1);
			$this->SetX( 123 );											# Order
			$this->Cell(NULL, $h, $v['order']);
			$this->SetX( 202 );											# Pending estimate
			$this->Cell(NULL, $h, $v['pendingEstimate'] ? 'X' : '');
			$this->SetY( 153 );
			switch( $v['quality'] ){								# Quality
				case 'bad':			$this->SetX( 98 ); break;
				case 'regular':		$this->SetX( 134 ); break;
				case 'good':		$this->SetX( 165 ); break;
				case 'excellent':	$this->SetX( 202 ); break;
			}
			$this->Cell(NULL, NULL, $v['quality'] ? 'X' : '');
			
			# WORKING PERIOD
			$this->SetFontSize( 16 );
			$this->SetXY(130, 163.4);
			$this->Cell(10, 7, $period['startsH'], 0, 0, 'R');			# Starts
			$this->Cell(1.5, 7, '');
			$this->Cell(44, 7, $period['startsM']);
			$this->Cell(10, 7, $period['endsH'], 0, 0, 'R');			# Ends
			$this->Cell(1.5, 7, '');
			$this->Cell(10, 7, $period['endsM']);
			
			
		}

		private function rules(){
		
			# Upper box
			$this->SetXY(16, 19);
			$this->Cell(0, 14.5, '', 1);
			# Mid box
			$this->SetXY(16, 37.5);
			$this->Cell(0, 36.5, '', 1);
			# Lower box
			$this->SetXY(16.5, 77);
			$this->Cell(0, 88.4, '', 1);
			
		}
		
	}

?>