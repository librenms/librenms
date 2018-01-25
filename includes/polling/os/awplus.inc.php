<?php

//$hardware and $serial use snmp_getnext as the OID for these is not always fixed.
//However, the first OID is the device baseboard.

$hardware = snmp_getnext($device, "rscBoardName", "-OQv", "AT-RESOURCE-MIB");
$version = snmp_get($device, "currSoftVersion.0", "-OQv", "AT-SETUP-MIB");
$hostname = snmp_get($device, "sysName.0", "-OQv", "SNMPv2-MIB");
$serial = snmp_getnext($device, "rscBoardSerialNumber", "-OQv", "AT-RESOURCE-MIB");

// SBx8100 platform has line cards show up first in "rscBoardName" above.
//Instead use sysObjectID.0
$data = snmp_get_multi_oid($device, 'rscBoardName.5.6 rscBoardName.6.6 rscBoardSerialNumber.5.6 rscBoardSerialNumber.6.6', '-OQs', 'AT-RESOURCE-MIB');

if (strpos($hardware, 'SBx81') !== false) {
    $hardware = snmp_get($device, "sysObjectID.0", "-OQvs", "SNMPv2-MIB:AT-PRODUCT-MIB");
    $hardware = str_replace('at', 'AT-', $hardware);
// Features and Serial is set to Controller card  1.5
    $features = $data['rscBoardName.5.6'];
    $serial = $data['rscBoardSerialNumber.5.6'];
// If bay 1.5 is empty, set to Controller card 1.6
    if (!$features && !$serial) {
        $features = $data['rscBoardName.6.6'];
        $serial = $data['rscBoardSerialNumber.6.6'];
    }
}
