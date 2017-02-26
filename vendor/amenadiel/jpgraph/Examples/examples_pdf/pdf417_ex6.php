<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/pdf417/jpgraph_pdf417.php');

$data1 = '12345';
$data2 = 'Abcdef';
$data3 = '6789';

// Manually specify several encodation schema
$data = array(
    array(USE_NC,$data1),
    array(USE_TC,$data2),
    array(USE_NC,$data3));

//$data = "12345Abcdef6789";

// Setup some symbolic names for barcode specification

$columns = 8;   // Use 8 data (payload) columns
$modwidth = 2;  // Use 2 pixel module width
$errlevel = 2;  // Use error level 2
$showtext = true;  // Show human readable string

try {
	// Create a new encoder and backend to generate PNG images
	$encoder = new PDF417Barcode($columns,$errlevel);
	$backend = PDF417BackendFactory::Create(BACKEND_IMAGE,$encoder);

	$backend->SetModuleWidth($modwidth);
    $backend->NoText(!$showtext);
    $backend->Stroke($data);
}
catch(JpGraphException $e) {
	echo 'PDF417 Error: '.$e->GetMessage();
}
?>
