<?php
    // Example 10 : Specified error correction level

    // Include the library
    require_once ('jpgraph/QR/qrencoder.inc.php');

    $data      = 'ABCDEFGH01234567'; // Data to be encoded
    $version   = -1;  // -1 = Let the library decide version (same as default)
    $corrlevel = QRCapacity::ErrH; // Error correction level H (Highest possible)

    // Create a new instance of the encoder using the specified
    // QR version and error correction
    $encoder = new QREncoder($version,$corrlevel);

    // Use the image backend
    $backend = QRCodeBackendFactory::Create($encoder, BACKEND_IMAGE);

    // Set the module size
    $backend->SetModuleWidth(3);

    // Set color
    $backend->SetColor('brown','white');

    // Store the barcode in the specifed file
    $backend->Stroke($data);
?>
