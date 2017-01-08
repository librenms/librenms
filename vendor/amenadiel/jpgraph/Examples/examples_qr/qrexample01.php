<?php
require_once ('jpgraph/QR/qrencoder.inc.php');

// Data to be encoded
$data = '01234567';

// Create a new instance of the encoder and let the library
// decide a suitable QR version and error level
$encoder = new QREncoder();

// Use the image backend
$backend = QRCodeBackendFactory::Create($encoder, BACKEND_IMAGE);

// Set the module size (quite big)
$backend->SetModuleWidth(5);

// .. send the barcode back to the browser for the data
$backend->Stroke($data);
?>
