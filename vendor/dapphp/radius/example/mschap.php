<?php

/**
 * RADIUS client example using MS-CHAPv1.
 *
 * Tested with Windows Server 2012 R2 Network Policy Server
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../autoload.php';

$radius = new \Dapphp\Radius\Radius();
$radius->setServer('192.168.0.20')     // IP or hostname of RADIUS server
       ->setSecret('xyzzy5461')        // RADIUS shared secret
       ->setNasIpAddress('127.0.0.1')  // IP or hostname of NAS (device authenticating user)
       ->setNasPort(20);               // NAS port

$radius->setMSChapPassword('arctangent123$'); // set mschapv1 password for user

// Send access request for user nemo
$response = $radius->accessRequest('nemo');

if ($response === false) {
    // false returned on failure
    echo sprintf("Access-Request failed with error %d (%s).\n",
        $radius->getErrorCode(),
        $radius->getErrorMessage()
        );
} else {
    // access request was accepted - client authenticated successfully
    echo "Success!  Received Access-Accept response from RADIUS server.\n";
}
