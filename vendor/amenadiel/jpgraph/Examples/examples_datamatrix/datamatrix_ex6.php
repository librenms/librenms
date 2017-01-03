<?php
require_once('jpgraph/datamatrix/datamatrix.inc.php');

$data = 'This is a datamatrix symbol';

$outputfile = 'dm_ex6.png';

// Create and set parameters for the encoder
$encoder = DatamatrixFactory::Create();
$encoder->SetEncoding(ENCODING_TEXT);

// Create the image backend (default)
$backend = DatamatrixBackendFactory::Create($encoder);
$backend->SetModuleWidth(5);
$backend->SetQuietZone(10);

// Set other than default colors (one, zero, background)
$backend->SetColor('navy','white');

// Create the barcode from the given data string and write to output file
$dir = dirname(__FILE__);
$file = '<span style="font-weight:bold;">"'.$dir.'/'.$outputfile.'"</span>';
try {
    $backend->Stroke($data,$outputfile);
    echo 'Barcode sucessfully written to file: '.$file;
} catch (Exception $e) {
    $errstr = $e->GetMessage();
    $errcode = $e->GetCode();
    echo "Failed writing file: ".$file.'<br>';
    echo "Datamatrix error ($errcode). Message: $errstr\n";
}

?>
