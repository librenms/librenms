<?php
    // Example 3 : QR Barcode with specified version and stored to a file

    // Include the library
    require_once ('jpgraph/QR/qrencoder.inc.php');

    // Data to be encoded
    $data = '01234567';
    $version = 3;  // Use QR version 3
    $fileName = 'qrexample03.png';

    // Create a new instance of the encoder and let the library
    // decide a suitable error level
    $encoder = new QREncoder($version);

    // Use the image backend
    $backend = QRCodeBackendFactory::Create($encoder, BACKEND_IMAGE);

    // Set the module size (quite big)
    $backend->SetModuleWidth(5);

    // Store the barcode in the specifed file
    $backend->Stroke($data,$fileName);
    list($version,$errorcorrection) = $backend->GetQRInfo();

    echo "QR Barcode, (<b>Version: $version-$errorcorrection</b>), image stored in file $fileName";
?>
