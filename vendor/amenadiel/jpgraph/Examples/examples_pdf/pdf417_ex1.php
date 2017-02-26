<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/pdf417/jpgraph_pdf417.php');

$data = 'PDF-417';
try {
	// Create a new encoder and backend to generate PNG images
	$backend = PDF417BackendFactory::Create(BACKEND_IMAGE,new PDF417Barcode());
	$backend->Stroke($data);
}
catch(JpGraphException $e) {
	echo 'PDF417 Error: '.$e->GetMessage();
}
?>
