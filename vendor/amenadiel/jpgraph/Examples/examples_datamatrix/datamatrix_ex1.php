<?php
require_once('jpgraph/datamatrix/datamatrix.inc.php');

$data = '123456';

$encoder = DatamatrixFactory::Create();
$backend = DatamatrixBackendFactory::Create($encoder);
$backend->SetModuleWidth(3);

// Create the barcode from the given data string and write to output file
try {
    $backend->Stroke($data);
} catch (Exception $e) {
    $errstr = $e->GetMessage();
    echo "Datamatrix error message: $errstr\n";
}

?>
