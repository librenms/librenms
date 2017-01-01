<?php
require_once('jpgraph/datamatrix/datamatrix.inc.php');

$data = 'A Datamatrix barcode';

// Create and set parameters for the encoder
$encoder = DatamatrixFactory::Create();
$encoder->SetEncoding(ENCODING_BASE256);

// Create the image backend (default)
$backend = DatamatrixBackendFactory::Create($encoder, BACKEND_ASCII);
$backend->SetModuleWidth(3);

try {
    $ps_txt = $backend->Stroke($data);
    echo '<pre>'.$ps_txt.'</pre>';
} catch (Exception $e) {
    $errstr = $e->GetMessage();
    echo "Datamatrix error message: $errstr\n";
}

?>
