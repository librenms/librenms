<?php
require_once ('jpgraph/QR/qrencoder.inc.php');

// Data to be encoded
$data         = 'ABCDEFGH01234567';

// QR Code specification
$version      = -1;  				// -1 = Let the library decide version (same as default)
$corrlevel    = QRCapacity::ErrM;   // Medium erro correction
$modulewidth  = 2;					// Module width
$back         = BACKEND_IMAGE;		// Default backend
$quiet		  = 4; 					// Same as default value

// Create encoder and backend
$encoder = new QREncoder($version, $corrlevel);
$backend = QRCodeBackendFactory::Create($encoder, $back);

// Set the module size
$backend->SetModuleWidth($modulewidth);

// Set Quiet zone (this should rarely need changing from the default)
$backend->SetQuietZone($quiet);

if( $back == BACKEND_IMAGE ) {

	$backend->Stroke($data);
}
else {
	$str = $backend->Stroke($data);
	echo '<pre>'.$str.'</pre>';
}
?>
