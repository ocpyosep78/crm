<?php

require_once(dirname(__FILE__).'/fPDF.class.php');

/**
 * Border by default for cells created with method Cell
 * Define PDF_CELL_BORDER as 1 for designing/debugging
 */
if( !defined('PDF_CELL_BORDER') ) define('PDF_CELL_BORDER', 0);



class ExtendedFPDF extends FPDF{
	
	var $multiCol;

	var $angle=0;

	var $headerTitle;		# Headers title
	var $cPadding;			# Cell padding (for auto-width and auto-height only)
	

	public function __construct($orientation='P', $unit='mm', $format='A4'){
	
		parent::__construct($orientation, $unit, $format);
		
		# Cell Padding (1 mm)
		$this->cPadding = 2.835 / $this->k;
		
		# Headers Title
		$this->headerTitle = 'Title';
		
		# Set MultiCol keys to their default values
		$this->EndMultiCol();
		
	}

	public function DefFont($family='', $style='', $size=0, $color=NULL, $bgColor=NULL){
	
		$this->SetFont($family, $style, $size);
		
		if( is_array($color) ) call_user_func_array(array($this, 'SetTextColor'), $color);
		elseif( $color ) $this->SetTextColor($color);
		
		if( is_array($bgColor) ) call_user_func_array(array($this, 'SetFillColor'), $bgColor);
		elseif( $bgColor ) $this->SetFillColor($bgColor);
		
	}

	public function SetPadding( $pad ){
		$this->cPadding = $pad;
	}

	public function SetHeaderTitle( $title='Title' ){
		$this->headerTitle = $title;
	}
	
	
/***************
** NATIVE METHODS EXTENSION
***************/
	
	public function Cell($w, $h=0, $txt='', $border=NULL, $ln=0, $align='', $fill=false, $link=''){
	
		if($w === NULL) $w = $this->GetStringWidth($txt) + $this->cPadding * 2 + (is_null($border) ? 2 : 0);
		if($h === NULL) $h = $this->FontSizePt / $this->k + $this->cPadding * 2;
		if($border === NULL ) $border = PDF_CELL_BORDER;
		
		return parent::Cell($w, $h, $txt, $border, $ln, strtoupper($align), $fill, $link);
		
	}
	
	public function MultiCell($w, $h=NULL, $txt='', $border=NULL, $align='J', $fill=false){
	
		$maxWidth = $this->w - $this->rMargin - $this->x;
		if($w === NULL) $w = min($maxWidth, $this->GetStringWidth($txt) + $this->cPadding * 2 + 3);
		if($h === NULL) $h = $this->FontSizePt / $this->k + $this->cPadding * 2 + 2;
		if($border === NULL ) $border = PDF_CELL_BORDER;
		
		parent::MultiCell($w, $h, $txt, $border, strtoupper($align), $fill);
		
	}
	
	public function AcceptPageBreak(){
	
		if( $this->GetMultiCol('col') + 1 >= $this->GetMultiCol('cols') ){
			$this->SetMultiCol('col', 0);
		}
		else{
			# Store new Column number
			$this->SetMultiCol('col', $this->GetMultiCol('col') + 1);
			# Calculate column width and corresponding right margin
			$this->SetMultiColMargins();
			# Go back 'up'
		}
		
		$this->SetX( $this->lMargin );
		$this->SetY( $this->GetMultiCol('y0') );

		return $this->GetMultiCol('col') == 0;
		
	}
	
/***************
** IMAGES
***************/
	
	function FramedImage($file, $x=NULL, $y=NULL, $limit, $type='', $link=''){
	
		$size = getimagesize($file);
		$ratio = $limit / ($size[0] < $size[1] ? $size[1] : $size[0]);
		$dim = array($size[0] * $ratio, $size[1] * $ratio);
		
		$dx = $x + ($limit - $dim[0]) / 2;
		$dy = $y + ($limit - $dim[1]) / 2;
		
		return $this->Image($file, $dx, $dy, $dim[0], $dim[1], $type, $link);
		
	}
	
	public function BackgroundImage($img, $stretch, $margin=0){
	
		$dims = array(
			'x' => $stretch ? $margin : $this->lMargin,
			'y' => $stretch ? $margin : $this->tMargin,
			'w' => $stretch ? $this->w - 2*$margin : $this->w - ($this->rMargin + $this->lMargin),
			'h' => $stretch ? $this->h - 2*$margin : $this->h - ($this->bMargin + $this->tMargin),
		);
		$this->Image($img, $dims['x'], $dims['y'], $dims['w'], $dims['h']);
		
	}
	
/***************
** ROTATED TEXT
***************/

	public function Rotate($angle, $x=-1, $y=-1){
	
		if( $x == -1 ) $x = $this->x;
		if( $y == -1 ) $y = $this->y;
		if( $this->angle != 0 ) $this->_out('Q');
		
		if( $this->angle=$angle ){
			$angle *= M_PI / 180;
			$c = cos( $angle );
			$s = sin( $angle );
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(sprintf(
				'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',
				$c, $s, -$s, $c, $cx, $cy, -$cx, -$cy
			));
		}
		
	}
	
	public function RotatedText($x, $y, $txt, $angle){
	
		# Text rotated around its origin
		$this->Rotate($angle, $x, $y);
		$this->Text($x, $y, $txt);
		$this->Rotate( 0 );
		
	}
	
	public function RotatedImage($file, $x, $y, $w, $h, $angle){
	
		# Image rotated around its upper-left corner
		$this->Rotate($angle, $x, $y);
		$this->Image($file, $x, $y, $w, $h);
		$this->Rotate( 0 );
		
	}
	
	
/***************
** TABLES
***************/

	/**
	 * Simple table
	 */
	public function BasicTable($header, $data){
	
		# Header
		foreach( $header as $col ) $this->Cell(40, 7, $col, 1);
		$this->Ln();
		
		# Data
		foreach( $data as $row ){
			foreach($row as $col) $this->Cell(40, 6, $col, 1);
			$this->Ln();
		}
		
	}

	/**
	 * Better table
	 */
	public function ImprovedTable($header, $data){
	
		# Column widths
		$w = array(40, 35, 40, 45);
		
		# Header
		for( $i=0 ; $i<count($header) ; $i++ ) $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
		$this->Ln();
		
		# Data
		foreach($data as $row){
			$this->Cell($w[0], 6, $row[0], 'LR');
			$this->Cell($w[1], 6, $row[1], 'LR');
			$this->Cell($w[2], 6, number_format($row[2]), 'LR', 0, 'R');
			$this->Cell($w[3], 6, number_format($row[3]), 'LR', 0, 'R');
			$this->Ln();
		}
		
		# Closure line
		$this->Cell(array_sum($w), 0, '', 'T');
		
	}

	/**
	 * Colored table
	 */
	public function FancyTable($header, $data){
	
		# Colors, line width and bold font
		$this->SetFillColor(255, 0, 0);
		$this->SetTextColor(255);
		$this->SetDrawColor(128, 0, 0);
		$this->SetLineWidth(.3);
		$this->SetFont('', 'B');
		
		# Header
		$w = array(40, 35, 40, 45);
		for( $i=0 ; $i<count($header) ; $i++ ) $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
		$this->Ln();
		
		# Color and font restoration
		$this->SetFillColor(224, 235, 255);
		$this->SetTextColor(0);
		$this->SetFont('');
		
		# Data
		$fill = false;
		foreach( $data as $row ){
			$this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
			$this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
			$this->Cell($w[2], 6, number_format($row[2]), 'LR', 0, 'R', $fill);
			$this->Cell($w[3], 6, number_format($row[3]), 'LR', 0, 'R', $fill);
			$this->Ln();
			$fill = !$fill;
		}
		$this->Cell(array_sum($w), 0, '', 'T');
		
	}

	
/***************
** STRINGS
***************/

	/**
	 * Fill a string with spaces untill it fills $lines lines of $width width each
	 * If given string is too long, it is cut instead
	 */
	protected function fillStringForLines($w, $toLines, &$txt){
	
		# Find out how many chars we can keep and apply changes
		$text = $tmp = $txt;
		$width = 0;
		$lines = 0;
		$lns = array();
		while( $lines++ < $toLines && $text && !($ln='') && !($i=0) ){
			while( $ln != $text && $this->GetStringWidth($ln.$text[$i]) < $w ) $ln .= $text[$i++];
			# Attempt to cut it between words if possible
			if( $text[$i] && $text[$i] != ' ' && preg_match('/(.+) +[^ ]*$/', $ln, $new) ) $ln = $new[1];
			$width += strlen( $ln );
			$text = substr($txt, $width);
			$lns[$lines] = $ln;
		}
		$tmp = substr($txt, 0, $width);
		
		# See how many lines it fills (could be shorter from the start)
		if( $lines <= $toLines ){
			$empty = ' ';		# Build a line solely composed by spaces
			while( $this->GetStringWidth($empty) < $w ) $empty .= ' ';
			while( $lines++ <= $toLines ) $tmp .= $empty;
		}
		
		# See if we had to cut the string, and if so add a [...]
		if( $tmp < $txt ){
			$maxWidth = $this->GetStringWidth( $tmp ) - 5;
			while( $this->GetStringWidth($tmp.' [...]') > $maxWidth ) $tmp = substr($tmp, 0, -1);
			$tmp .= ' [...]';
		}
		
		return $txt = $tmp;
	
	
		return;
		
		$concat = $this->GetStringWidth($text) > $maxStrWidth ? ' [...]' : '';
		
		while( $this->GetStringWidth($text) > $maxStrWidth ){
			$text = substr($text, 0, -1);
		}
			
		while( $this->GetStringWidth($text) < $minStrWidth ){
			$text .= ' ';
		}
		
		return $text .= $concat;
		
	}

	/**
	 * Print a multicell with fixed height and width, filling string or removing
	 * characters when needed.
	 */
	protected function FixedCell($lines, $w, $h, $txt='', $border=NULL, $align='J', $fill=false){
	
		$this->fillStringForLines($w, $lines, $txt);
		$this->MultiCell($w, $h/$lines, $txt, $border, $align, $fill);
		
	}
	
	protected function limitLength($data, $len){
	
		$extraLen = $this->GetStringWidth( '...' );
		
		if( is_array($data) ){
			foreach( $data as &$val ) $val = $this->limitLength($val, $len);
		}
		else{
			if( $this->GetStringWidth($data) > $len ){
				while( $this->GetStringWidth($data) > ($len - $extraLen) ){
					$data = substr($data, 0, -1);
				}
				$data .= '...';
			}
		}
		
		return $data;
		
	}

	
/***************
** MULTI COLUMNS
***************/

	protected function StartMultiCol($cols=NULL, $y0=NULL, $y1=NULL, $sep=3){
		
		if( empty($cols) ) $cols = 2;
		
		# Set control vars
		$this->multiCol = array(
			'y0'		=> !is_null($y0) ? $y0 : $this->tMargin,
			'y1'		=> !is_null($y1) ? $y1 : $this->bMargin,
			'cols'		=> $cols,
			'col'		=> 0,
			'sep'		=> $sep,
			'lMargin'	=> $this->lMargin,
			'rMargin'	=> $this->rMargin,
			'tMargin'	=> $this->tMargin,
			'bMargin'	=> $this->bMargin,
		);
		
		# Get column width
		$usableWidth = $this->w - $this->lMargin - $this->rMargin;
		$this->multiCol['width'] = ($usableWidth - $sep * ($cols - 1)) / $cols;
		
		# Position cursor
		$this->SetXY($this->lMargin, $y0);
		
		# Calculate column width and corresponding right margin
		$this->SetMultiColMargins();
		
		# Fix margins
		$this->tMargin = $this->multiCol['y0'];
		$this->SetAutoPageBreak(true, $this->h - ($this->multiCol['y0'] + $this->multiCol['y1']));
		
	}
	
	private function SetMultiColMargins(){
	
		$atts = $this->multiCol;
		
		# Left Margin
		$this->lMargin = $atts['lMargin'] + $atts['col'] * ($atts['width'] + $atts['sep']);
		# Right Margin
		$this->rMargin = $this->w - ($this->lMargin + $atts['width']);
		
	}

	protected function EndMultiCol(){
		
		# Restore previous parameters
		if( !is_null($this->GetMultiCol('lMargin')) ) $this->lMargin = $this->GetMultiCol('lMargin');
		if( !is_null($this->GetMultiCol('rMargin')) ) $this->rMargin = $this->GetMultiCol('rMargin');
		if( !is_null($this->GetMultiCol('tMargin')) ) $this->tMargin = $this->GetMultiCol('tMargin');
		if( !is_null($this->GetMultiCol('bMargin')) ) $this->bMargin = $this->GetMultiCol('bMargin');
		
		$this->SetAutoPageBreak(true, $this->bMargin);
		
		# Line feed to jump out of the last column
		if( isset($this->multiCol['y0']) && !is_null($this->multiCol['y0']) ){
			$this->SetXY($this->lMargin, $this->multiCol['y0'] + $this->multiCol['y1']);
		}
		
		# Set control vars to default values
		$this->multiCol = array(
			'y0'		=> 0,
			'y1'		=> 0,
			'cols'		=> 1,
			'col'		=> 0,
			'sep'		=> 0,
			'lMargin'	=> $this->lMargin,
			'rMargin'	=> $this->rMargin,
			'tMargin'	=> $this->tMargin,
			'bMargin'	=> $this->bMargin,
		);
		
	}
	
	protected function SetMultiCol($key, $val){
	
		return $this->multiCol[$key] = $val;
		
	}
	
	protected function GetMultiCol( $key ){
	
		return isset($this->multiCol[$key]) ? $this->multiCol[$key] : NULL;
		
	}
	
}

?>