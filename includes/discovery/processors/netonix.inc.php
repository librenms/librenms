<?php
if ($device['os'] == 'netonix') {
    echo 'NETONIX : ';

//    $system = snmp_get($device, 'ssCpuSystem.0', '-OvQ', 'UCD-SNMP-MIB');
//    $user   = snmp_get($device, 'ssCpuUser.0', '-OvQ', 'UCD-SNMP-MIB');
    $idle   = snmp_get($device, 'ssCpuIdle.0', '-OvQ', 'UCD-SNMP-MIB');

    if (is_numeric($idle)) {
        discover_processor($valid['processor'], $device, 0, 0, 'ucd-old', 'CPU', '1', (100 - $idle));
    }
}
