<?php

if (preg_match('/^Cisco IOS XR Software \(Cisco ([^\)]+)\),\s+Version ([^\[]+)\[([^\]]+)\]/', $device['sysDescr'], $regexp_result)) {
    $hardware = $regexp_result[1];
    $features = $regexp_result[3];
    $version  = $regexp_result[2];
} elseif (preg_match('/^Cisco IOS XR Software \(([^\)]+)\),\s+Version\s+([^\s]+)/', $device['sysDescr'], $regexp_result)) {
    $hardware = $regexp_result[1];
    $version  = $regexp_result[2];
} else {
    // It is not an IOS-XR ... What should we do ?
}

$oids = ['entPhysicalSoftwareRev.1', 'entPhysicalModelName.8384513', 'entPhysicalModelName.8384518'];
$data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB');

if (isset($data[1]['entPhysicalSoftwareRev']) && !empty($data[1]['entPhysicalSoftwareRev'])) {
    $version = $data[1]['entPhysicalSoftwareRev'];
}

if (isset($data[8384518]['entPhysicalModelName']) && !empty($data[8384513]['entPhysicalModelName'])) {
    $hardware = $data[8384513]['entPhysicalModelName'];
} elseif (isset($data[8384518]['entPhysicalModelName']) && !empty($data[8384518]['entPhysicalModelName'])) {
    $hardware = $data[8384518]['entPhysicalModelName'];
}

$serial = get_main_serial($device);

echo "\n".$device['sysDescr']."\n";
