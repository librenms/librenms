<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/pdf417/jpgraph_pdf417.php');

$data = 'PDF-417';

// Setup some symbolic names for barcode specification

$columns = 8;   // Use 8 data (payload) columns
$errlevel = 4;  // Use error level 4
$modwidth = 0.8;// Setup module width (in PS points)
$height = 3;    // Height factor (=2)
$showtext = true;  // Show human readable string

try {
	// Create a new encoder and backend to generate PNG images
	$encoder = new PDF417Barcode($columns,$errlevel);
	$backend = PDF417BackendFactory::Create(BACKEND_PS,$encoder);

	$backend->SetModuleWidth($modwidth);
    $backend->SetHeight($height);
    $backend->NoText(!$showtext);
    $backend->SetColor('black','yellow');
    $output = $backend->Stroke($data);
    echo nl2br(htmlspecialchars($output));
}
catch(JpGraphException $e) {
	echo 'PDF417 Error: '.$e->GetMessage();
}
?>
