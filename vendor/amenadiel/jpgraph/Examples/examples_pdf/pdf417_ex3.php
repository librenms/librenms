<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/pdf417/jpgraph_pdf417.php');

$data = 'PDF-417';

// Setup some symbolic names for barcode specification

$columns = 8;   // Use 8 data (payload) columns
$errlevel = 4;  // Use error level 4
$modwidth = 2;  // Setup module width (in pixels)
$height = 2;    // Height factor (=2)
$showtext = false;  // Show human readable string

// Create a new encoder and backend to generate PNG images
try {
	$encoder = new PDF417Barcode($columns,$errlevel);
	$backend = PDF417BackendFactory::Create(BACKEND_IMAGE,$encoder);
    $backend->SetModuleWidth($modwidth);
    $backend->SetHeight($height);
    $backend->NoText(!$showtext);
    $backend->Stroke($data);
}
catch(JpGraphException $e) {
	echo 'PDF417 Error: '.$e->GetMessage();
}
?>
