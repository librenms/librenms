<?php

if (!empty($matches[2])) {
    $version .= " (" . trim($matches[2]) . ")";
}

// List of OIDs for HW recognition, add any potential HW OID here. 
$hwOidList = [
    '.1.3.6.1.4.1.27975.37.1.5.1.4.1.1',
 ];
foreach ($hwOidList as $oid) {
    $hardware_tmp = snmp_get($device, $oid, '-OQv');
    if (!empty($hardware_tmp)) {
        $hardware = $hardware_tmp;
    }
}

// List of OIDs for version, add any potential OID here.
// As the mib is really buggy, let's use numeric OID for now
$verOidList = [
    '.1.3.6.1.4.1.27975.1.3.5.0', //SWITCH::version.0
];
foreach ($verOidList as $oid) {
    $version_tmp = snmp_get($device, $oid, '-OQv');
    if (!empty($version_tmp)) {
        $version = $version_tmp;
        break;
    }
}

//List of OIDs for SN, add any potential device SN OID here
$snOidList = [
    '.1.3.6.1.4.1.27975.37.1.5.1.10.1.1',
];
foreach ($snOidList as $oid) {
    $serial_tmp = snmp_get($device, $oid, '-OQv');

    if (!empty($serial_tmp)) {
        $serial = $serial_tmp;
        break;
    }
}
