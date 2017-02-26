<?php
    // Example 6 : QR Barcode with image in JPG format

    // Include the library
    require_once ('jpgraph/QR/qrencoder.inc.php');

    $data      = 'ABCDEFGH01234567'; // Data to be encoded
    $version   = -1;  // -1 = Let the library decide version (same as default)
    $corrlevel = -1; // -1 = Let the library decide error correction level (same as default)

    // Create a new instance of the encoder using the specified
    // QR version and error correction
    $encoder = new QREncoder($version,$corrlevel);

    // Use the image backend
    $backend=QRCodeBackendFactory::Create($encoder, BACKEND_IMAGE);

    // Use JPEG format with 80% quality level
    $backend->SetImgFormat('jpeg',80);

    // Set the module size
    $backend->SetModuleWidth(4);

    // Store the barcode in the specifed file
    $backend->Stroke($data);
?>
