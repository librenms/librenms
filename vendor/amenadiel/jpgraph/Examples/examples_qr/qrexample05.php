<?php
    // Example 5 : QR Barcode with manually specified encodation

    // Include the library
    require_once ('jpgraph/QR/qrencoder.inc.php');

    // Data to be encoded
    // We want the data to be encoded using alphanumeric encoding even though
    // it is only numbers
    $data = array(
        array(QREncoder::MODE_ALPHANUM,'01234567')
    );

    $version = 3;  // Use QR version 3
    $corrlevel = QRCapacity::ErrH ; // Level H error correction (the highest possible)

    // Create a new instance of the encoder using the specified
    // QR version and error correction
    $encoder = new QREncoder($version,$corrlevel);

    // Use the image backend
    $backend = QRCodeBackendFactory::Create($encoder, BACKEND_IMAGE);

    // Set the module size
    $backend->SetModuleWidth(4);

    // Store the barcode in the specifed file
    $backend->Stroke($data);
?>
