<?php

$count = dbFetchCell("SELECT COUNT(*) FROM `processors` WHERE `device_id` = ? AND `processor_type` != 'ucd-old'", array($device['device_id']));

if (($device['os_group'] == 'unix' && $count == '0') || $device['os'] === 'kemp') {
    echo 'UCD Old: ';

    $system = snmp_get($device, 'ssCpuSystem.0', '-OvQ', 'UCD-SNMP-MIB');
    $user   = snmp_get($device, 'ssCpuUser.0', '-OvQ', 'UCD-SNMP-MIB');
    $idle   = snmp_get($device, 'ssCpuIdle.0', '-OvQ', 'UCD-SNMP-MIB');

    if (is_numeric($system)) {
        $percent = ($system + $user + $idle);
        discover_processor($valid['processor'], $device, 0, 0, 'ucd-old', 'CPU', '1', ($system + $user), null, null);
    }
}
