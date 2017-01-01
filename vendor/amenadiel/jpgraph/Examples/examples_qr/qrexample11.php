<?php
// Include the library
require_once ('jpgraph/QR/qrencoder.inc.php');

// Example 11 : Generate postscript output

$data         = 'ABCDEFGH01234567'; // Data to be encoded
$version      = -1;  // -1 = Let the library decide version (same as default)
$corrlevel    = QRCapacity::ErrH; // Error correction level H (Highest possible)
$modulewidth  = 3;

// Create a new instance of the encoder using the specified
// QR version and error correction
$encoder = new QREncoder($version,$corrlevel);

// Use the image backend
$backend = QRCodeBackendFactory::Create($encoder, BACKEND_PS);

// Set the module size
$backend->SetModuleWidth($modulewidth);

// Store the barcode in the specifed file
$ps_str = $backend->Stroke($data);

echo '<pre>'.$ps_str.'</pre>';
?>
