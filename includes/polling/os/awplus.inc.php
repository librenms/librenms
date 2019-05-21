<?php

//$hardware and $serial use snmp_getnext as the OID for these is not always fixed.
//However, the first OID is the device baseboard.

$data = snmp_getnext_multi($device, 'rscBoardName rscBoardSerialNumber', '-OQs', 'AT-RESOURCE-MIB');
$hardware = $data['rscBoardName'];
$serial = $data['rscBoardSerialNumber'];
$version = snmp_get($device, "currSoftVersion.0", "-OQv", "AT-SETUP-MIB");

// SBx8100 platform has line cards show up first in "rscBoardName" above.
//Instead use sysObjectID.0

if (strpos($hardware, 'SBx81') !== false) {
    $data_array = snmpwalk_cache_multi_oid($device, 'rscBoardName', $data_array, 'AT-RESOURCE-MIB', '-OUsb');
    $data_array = snmpwalk_cache_multi_oid($device, 'rscBoardSerialNumber', $data_array, 'AT-RESOURCE-MIB', '-OUsb');

    $hardware = snmp_translate($device['sysObjectID'], 'AT-PRODUCT-MIB', null, null, $device);
    $hardware = str_replace('at', 'AT-', $hardware);

// Features and Serial is set to Controller card 1.5
    $features = $data_array['5.6']['rscBoardName'];
    $serial = $data_array['5.6']['rscBoardSerialNumber'];

// If bay 1.5 is empty, set to Controller card 1.6
    if (!$features && !$serial) {
        $features = $data_array['6.6']['rscBoardName'];
        $serial = $data_array['6.6']['rscBoardSerialNumber'];
    }
}
