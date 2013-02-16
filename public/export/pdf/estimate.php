<?php

define('DEBUG', true);
define('PDF_CELL_BORDER', isset($_GET['debug']) ? 1 : 0);


# Required ID
!empty($_GET['id']) ? $id = $_GET['id'] : die('Faltan datos requeridos.');

# Initialize constants and libraries
require_once('../../../initialize.php');

# Block unauthorized access
Object::enforce('estimatePDF');

# Libraries for generating the PDF
require_once(dirname(__FILE__).'/estimateLib/PDF.Estimates.Drawer.class.php');

# PDF
$PDF = new PDF_Estimates_Drawer( $id );

# Validate this estimate's data (showResults forces a summary to be printed instead of the PDF)
$PDF->validate( isset($_GET['showResults']) );

# Build and Print pages
$PDF->display();

require_once EXPORT_PDF_PATH . '/estimate.php';