<?php

//Huawei VRP devices are not providing the HW description in a unified way

preg_match("/Version [^\s]*/m", $device['sysDescr'], $matches);
$version = trim(str_replace('Version ', '', $matches[0]));

preg_match("/\(([^\s]*) (V[0-9]{3}R[0-9]{3}[0-9A-Z]+)/m", $device['sysDescr'], $matches);

if (!empty($matches[2])) {
    $version .= " (" . trim($matches[2]) . ")";
}

$oidList = [
    'HUAWEI-ENTITY-EXTENT-MIB::hwEntityExtentMIB.6.5.0',
    'HUAWEI-DEVICE-EXT-MIB::hwProductName.0',
    'HUAWEI-MIB::hwDatacomm.183.1.25.1.5.1',
    'HUAWEI-MIB::mlsr.20.1.1.1.3.0',
];
foreach ($oidList as $oid) {
    $hardware_tmp = snmp_get($device, $oid, '-OQv');

    if (!empty($hardware_tmp)) {
        $hardware = "Huawei " . $hardware_tmp;
        break;
    }
}

// Let's use sysDescr if nothing else is found in the OIDs. sysDescr is less detailled than OIDs most of the time
if (empty($hardware_tmp) && !empty($matches[1])) {
    $hardware = "Huawei " . trim($matches[1]);
}
