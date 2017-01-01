<?php
require_once ('jpgraph/QR/qrencoder.inc.php');

// Data to be encoded
$data = '01234567';

// Create a new instance of the encoder and let the library
// decide a suitable QR version and error level
$encoder = new QREncoder(1);

// Use the image backend (this is also the default)
$backend = QRCodeBackendFactory::Create($encoder);

try {
	// 	. send the QR Code back to the browser
    $backend->Stroke($data);
} catch (Exception $e) {
    $errstr = $e->GetMessage();
    echo 'QR Code error: '.$e->GetMessage()."\n";
    exit(1);
}

?>