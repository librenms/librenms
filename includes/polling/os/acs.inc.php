<?php
 
$hardware = 'Virtual Machine';
$serial = str_replace('"', '', snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.11.1', '-Oqv'));

// Cisco Secure Access Control System 5.8
if (preg_match('/^Cisco Secure Access Control System ([^,]+)$/', $device['sysDescr'], $regexp_result)) {
    $version  = $regexp_result[1];
} else {
    $version = '';
}
