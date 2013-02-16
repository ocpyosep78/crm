<?php

# Debugging
define('PDF_CELL_BORDER', isset($_GET['debug']) ? 1 : 0);

# Required ID
!empty($_GET['id']) ? $id = $_GET['id'] : die('Faltan datos requeridos.');

# Initialize constants and libraries
require_once('../../../initialize.php');

# Block unauthorized access
Access::enforce('techVisitsInfo');

# Libraries for generating the PDF
require_once(dirname(__FILE__).'/techVisitLib/PDF.TechVisits.class.php');

# PDF
$PDF = new PDF_TechVisits( $id );
$PDF->forPrinting( isset($_GET['printer']) );
$PDF->forDownload( isset($_GET['download']) );

# Build and Print pages
$PDF->Display();