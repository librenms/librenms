<?php

// Sorry about the OIDs but there doesn't seem to be a matching MIB available... :-/
$response = snmp_get($device, '1.3.6.1.4.1.14125.100.1.8.0', '-OQv');
if (! empty($response)) {
    $version = 'Kernel ' . trim(snmp_get($device, '1.3.6.1.4.1.14125.100.1.8.0', '-OQv'), '" ');
    $version .= ' / Apps ' . trim(snmp_get($device, '1.3.6.1.4.1.14125.100.1.9.0', '-OQv'), '" ');
} else {
    $version = 'Firmware ' . trim(snmp_get($device, '1.3.6.1.4.1.14125.2.1.1.5.0', '-OQv'), '" ');
}
$serial = trim(snmp_get($device, '1.3.6.1.4.1.14125.100.1.7.0', '-OQv'), '" ');

// There doesn't seem to be a real hardware identification.. sysName will have to do?
$hw_response = snmp_get($device, '1.3.6.1.4.1.14125.100.1.6.0', '-OQv');
if (! empty($hw_response)) {
    $hardware = str_replace('EnGenius ', '', $device['sysName']) . ' v' . trim(snmp_get($device, '1.3.6.1.4.1.14125.100.1.6.0', '-OQv'), '" .');
} else {
    $hardware = $device['sysName'] . trim(snmp_get($device, '1.3.6.1.4.1.14125.3.1.1.5.0', '-OQv'), '" .');
}

$mode = snmp_get($device, '1.3.6.1.4.1.14125.100.1.4.0', '-OQv');
if (is_numeric($mode)) {
    switch ($mode) {
        case 0:
            $features = 'Router mode';
            break;
        case 1:
            $features = 'Universal repeater mode';
            break;
        case 2:
            $features = 'Access Point mode';
            break;
        case 3:
            $features = 'Client Bridge mode';
            break;
        case 4:
            $features = 'Client router mode';
            break;
        case 5:
            $features = 'WDS Bridge mode';
            break;
    }
}
