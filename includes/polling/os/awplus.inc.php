<?php

//$hardware and $serial use snmp_getnext as the OID for these is not always fixed.
//However, the first OID is the device baseboard.

$data = snmp_getnext_multi($device, 'rscBoardName rscBoardSerialNumber', '-OQs', 'AT-RESOURCE-MIB');
$hardware = $data['rscBoardName'];
$serial = $data['rscBoardSerialNumber'];

$version = snmp_get($device, "currSoftVersion.0", "-OQv", "AT-SETUP-MIB");

// SBx8100 platform has line cards show up first in "rscBoardName" above.
//Instead use sysObjectID.0
$data = snmp_get_multi_oid($device, 'rscBoardName.5.6 rscBoardName.6.6 rscBoardSerialNumber.5.6 rscBoardSerialNumber.6.6', '-OQs', 'AT-RESOURCE-MIB');

if (strpos($hardware, 'SBx81') !== false) {
    $hardware = snmp_translate($device['sysObjectID'], 'AT-PRODUCT-MIB', null, null, $device);
    $hardware = str_replace('at', 'AT-', $hardware);
// Features and Serial is set to Controller card 1.5
    $features = $data['rscBoardName.5.6'];
    $serial = $data['rscBoardSerialNumber.5.6'];
// If bay 1.5 is empty, set to Controller card 1.6
    if (!$features && !$serial) {
        $features = $data['rscBoardName.6.6'];
        $serial = $data['rscBoardSerialNumber.6.6'];
    }
}
