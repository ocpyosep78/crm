<?php

require_once THIRD_PARTY_PATH  . '/fPDF/ExtendedFPDF.class.php';
require_once dirname(__FILE__) . '/PDF.Estimates.class.php';
require_once dirname(__FILE__) . '/PDF.Estimates.SQL.class.php';
require_once dirname(__FILE__) . '/PDF.Estimates.Provider.class.php';


/**
* This class extends PDF_Estimates. While this one focuses on building pages (each
* having a name that determines its page code, i.e. drawIntro for page 'Intro'), the
* other one keeps global methods and extends fPDF extended library (thus giving this
* class all fPDF methods and down the chain).
*
* Page codes are defined de facto in PDF.Estimates.Provider.class.php, that is: in
* the object PDF_Estimates_Provider. To extend this library by adding new pages,
* write the corresponding method in this class (named drawMyNewPage) and add that
* code to some pages list(s) in PDF_Estimates_Provider::getPDFPages().
*/

class PDF_Estimates_Drawer extends PDF_Estimates{

	public function drawIntro(){

		# Get data this PDF depends on
		$cust = $this->cust();
		$estm = $this->data();
		$imgs = $this->DP->getCustImgs();

		# Store current config
		$marg = $this->rMargin;
		$size = $this->FontSizePt;

		# Add page with header and footer
		$this->SetHeaderTitle( $cust['legal_name'] );
		$this->AddPage();

		# Customer's image and basic info
		$this->FramedImage($imgs['mid'], 30, 40, 50);

		# Print order number
		$this->SetXY(0, 90);
		$this->SetFont(NULL, 'B', 14);
		$this->SetTextColor(0, 0, 120);
		$this->MultiCell(0, 6, "Nº DE ORDEN: {$estm['info']['orderNumber']}", NULL, 'R');

		# Print customer's info
		$this->SetXY($this->w/2, 50);
		$this->SetFont(NULL, '', 12);
		$this->SetTextColor(0, 0, 0);
		$this->rMargin = 30;
		$this->SetFontSize( 11 );
		$info = strtoupper($cust['customer'])."\n{$cust['address']} ({$cust['location']})\n{$cust['contact']}";
		$this->MultiCell(NULL, 6, $info, NULL, 'C');

		# Restore config
		$this->rMargin = $marg;
		$this->FontSizePt = $size;

		# Set next item's coordinates
		$this->SetY(100);

		# Get paragraphs from Data Provider and print them
		$paragraphs = $this->DP->getParagraphs( $this->DP->introParagraphs() );
		$this->printParagraphs( $paragraphs );

		# Print small customer's image
		$this->FramedImage($imgs['small'], 2*$this->w/3, 230, 30);

	}

	public function drawCCTV(){

		# Get estimate's list of items
		$estimate = $this->data( 'info' );
		$items = $this->data( 'items' );
		$products = $items['products'];
		$imgs = $this->DP->getCustImgs();

		# Products list might require 'zooming' (font and white-space) for large lists
		$zoom = $this->drawCCTV_zoom( count($products) );

		# Even zooming, more than 9 elements cannot be presented in the list
		if( count($products) > 18 ){
			$msg = 'La lista de productos (pág. 2) no puede contener más de 18 tipos de item. ';
			$msg .= 'Sólo se mostrarán los primeros 18 items, y no se mostrarán imágenes.';
			$this->warn( $msg );
		}

		# Add page with header and footer
		$this->SetHeaderTitle( $this->sys('system') );
		$this->AddPage();

		# Section title
		$cnt = count( $products );
		$this->setY( 25 + 10*$zoom );
		$this->SetFont('', 'B', 12*$zoom);
		$this->Cell(0, 10*$zoom, 'El sistema está compuesto por:', NULL, 1, 'C');
		if( $cnt < 7 ) $this->Ln((12 - $cnt) * 2);

		# Products list
		$this->SetY( $this->GetY() + 2*$zoom );
		$imgX = 0;
		$this->SetFont('', '', 10*$zoom);
		$imgsPrinted = array();
		foreach( $products as $item ){
			$this->SetFont('', 'B');
			$this->Write(6*$zoom, "* (x{$item['amount']}) {$item['name']}".($cnt > 5 ? '' : "\n"));
			$this->SetFont('', '');		# Clear bold
			$this->Write(6*$zoom, "  {$item['description']}");
			$this->Ln(($cnt < 13 ? 7 : 5) * $zoom);
			# Only 6 images should be printed (max)
			if( $cnt < 9 && count($imgsPrinted) < 6 && !in_array($item['code'], $imgsPrinted) ){
				$imgsPrinted[] = $item['code'];
				$path = "app/images/products/{$item['id_product']}.jpg";
				$this->FramedImage($path, ($imgX) * 31 + 12.5, 134, 28);
				$pos = array($this->GetX(), $this->GetY());
				$this->SetXY(($imgX++) * 31 + 12.5, 165);
				$this->SetTextColor(100, 0, 0);
				$this->SetFont('', 'B');
				$this->Cell(28, NULL, $item['code'], 0, 0, 'C');
				$this->SetXY($pos[0], $pos[1]);
				$this->SetTextColor(0, 0, 0);
				$this->SetFont('', '');
			}
		}

		# Frame product images
		$this->SetY( 122 );
		$this->Cell(0, 40, '');

		# Services and tools included in this estimate
		$this->SetY( 170 );
		$this->SetFont('', '', 11);
		$this->printParagraphs($this->DP->getParagraphs('cctvInclude'), 11, 9);

		# Warranty
		$this->SetY( 210 );
		$this->SetFontSize(12);
		$this->SubTitle( $this->DP->paragraphs('warranty') );
		$this->printParagraphs($this->DP->getParagraphs('changesNote'), 11, 9);

		# Payment, valid untill
		$this->SetY( -45 );
		$this->SetFont('Courier', 'B', '10');
		$this->SetFillColor( 220 );
		$this->Cell(0, 6, 'Modalidad de pago: a convenir', 'LRT', 1, 'L', 1);
		$this->Cell(0, 6, 'Presupuesto válido por 30 días', 'LRB', 1, 'L', 1);

	}

	/**
	 * Auxiliary function for drawCCTV
	 */
	private function drawCCTV_zoom( $cnt ){
		if( $cnt < 7 ) return 1;
		if( $cnt < 16 ) return 0.95;
		return 0.9 - ($cnt-16) / 20;
	}

	public function drawPlan(){

		$plan = $this->DP->getPlan();

		# Add page with header and footer
		$this->SetHeaderTitle( 'Planificación de Obras' );
		$this->AddPage();

		# Table of products and their locations / comments
		$this->SetY( 40 );
		$this->SetFont('Arial', '', 11);

		while( ($cnt=count($plan)) > 30 ) array_pop( $plan );
		$rows = ($cnt < 10) ? 3 : ($cnt < 16 ? 2 : 1);
		$h = 7;
		$this->SetX( 18 );
		$this->SetFont('', 'B', 16);
		$this->Cell(58, $h*$rows, 'Artículo', 1, 0, 'C');
		$this->Cell(20, $h*$rows, 'Cant.', 1, 0, 'C');
		$this->Cell(98, $h*$rows, 'Información', 1, 2, 'C');
		foreach( $plan as $row ){
			$this->SetX( 18 );
			$this->SetTextColor(0, 0, 90);
			$this->SetFont('', 'B', 12);
			$this->FixedCell($rows, 58, $h*$rows, $row['name'], 1, 'C');
			$this->SetXY(76, $this->GetY() - $h*$rows);
			$this->SetFont('', 'B', 18);
			$this->Cell(20, $h*$rows, $row['amount'], 1, 0, 'C');
			$this->SetTextColor( 100 );
			$this->SetFont('', '', 12);
			$this->FixedCell($rows, 98, $h*$rows, $row['position'], 1, 'L');
		}

	}

	public function drawServer(){

		$pgph = $this->DP->getParagraphs('server');

		# Add page with header and footer
		$this->SetHeaderTitle( 'Servidor' );
		$this->AddPage();
		$this->SetFont('Arial');

		# Monitor name
		$this->SetY(50);
		$this->SetFont('Arial', 'B', 18);
		$this->SetTextColor(150, 0, 0);
		$this->Cell(NULL, NULL, array_shift(array_keys($pgph)), NULL, 1);
		$this->Ln(5);

		# Monitor description
		$this->SetTextColor(0);
		$this->SetFont('Helvetica', '', 12);
		$this->MultiCell(110, 7, array_shift($pgph));

		# Images
		$this->Image('app/images/export/server_1.gif', $this->w - 125, 70, 125);
		$this->Image('app/images/export/server_2.gif', 10, 140, NULL, 130);

		# Disclaimer
		$this->SetXY(10, 252);
		$this->setTextColor( 90 );
		$this->SetFont('Arial', 'IB', 10);
		$this->Cell(0, 8, 'Nota: Las imágenes son de referencia y pueden no coincidir con el'.
			' modelo incluído en esta cotización', NULL, 1, 'R');

	}

	public function drawScreen(){

		$pgph = $this->DP->getParagraphs('screen');

		# Add page with header and footer
		$this->SetHeaderTitle( 'Monitor LCD' );
		$this->AddPage();

		# Monitor name
		$this->SetY(60);
		$this->SetFont('Arial', 'B', 18);
		$this->SetTextColor(150, 0, 0);
		$this->Cell(NULL, NULL, array_shift(array_keys($pgph)), NULL, 1);
		$this->Ln(5);

		# Monitor description
		$this->SetTextColor(0);
		$this->SetFont('Helvetica', '', 12);
		$this->MultiCell(110, 7, array_shift($pgph));

		# Images
		$this->Image('app/images/export/viewsonic.gif', $this->w - 135, 110, 125);
		$this->Image('app/images/export/viewsonic_sideview.gif', 10, 115, NULL, 130);

		# Disclaimer
		$this->SetXY(10, 252);
		$this->setTextColor( 90 );
		$this->SetFont('Arial', 'IB', 10);
		$this->Cell(0, 8, 'Nota: Las imágenes son de referencia y pueden no coincidir con el'.
			' modelo incluído en esta cotización', NULL, 1, 'R');

	}

	public function drawTotals(){

		# Add page with header and footer
		$this->SetHeaderTitle( 'Cotización' );
		$this->AddPage();
		$this->SetFont('Arial');

	}

	public function drawAbout(){

		# Add page with header and footer
		$this->SetHeaderTitle( 'Quiénes somos' );
		$this->AddPage();
		$this->SetFont('Arial');

		# Get paragraphs from Data Provider and print them
		$paragraphs = $this->DP->getParagraphs($this->DP->aboutParagraphs());
		$this->printParagraphs($paragraphs, 12, 10);

		# Customers list
		$this->SubTitle('Algunos de nuestros clientes', 12);
		$this->ln(2);
		$this->SetFontSize( 9 );
		$this->StartMultiCol(3, $this->GetY(), 80);
		$this->MultiCell(0, NULL, $this->DP->paragraphs('customers'), 0, 'L');
		$this->EndMultiCol();

		# References
		$this->MultiCell(NULL, NULL, 'Nuestras referencias son comprobables: no dude en contactarse con nuestros clientes.');

		# MI authorization
		$this->ln( 3 );
		$this->SetFont('Arial', 'B', 12);
		$this->MultiCell(0, NULL, 'Contamos con la habilitación de RE.NA.EM.SE (Ministerio del Interior)', 0, 'C');

		# Company's images
		$this->SetXY(-80, -70);
		$this->Image('app/images/export/company.jpg', NULL, NULL, 70, 40);
		$this->SetXY(20, -60);
		$this->Image('app/images/export/headerLogo.png', NULL, NULL, 70);

	}

}