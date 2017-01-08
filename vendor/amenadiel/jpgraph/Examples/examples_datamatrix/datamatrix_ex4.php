<?php
require_once('jpgraph/datamatrix/datamatrix.inc.php');

$data = 'This is a 64x64 datamatrix symbol';

// Create and set parameters for the encoder
$encoder = DatamatrixFactory::Create(DMAT_64x64);
$encoder->SetEncoding(ENCODING_TEXT);

// Create the image backend (default)
$backend = DatamatrixBackendFactory::Create($encoder);
$backend->SetModuleWidth(3);

// Adjust the Quiet zone
$backend->SetQuietZone(10);

// Create the barcode from the given data string and write to output file
try {
    $backend->Stroke($data);
} catch (Exception $e) {
    $errstr = $e->GetMessage();
    echo "Datamatrix error message: $errstr\n";
}

?>
