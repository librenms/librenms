<?php

// FIXME: EMD "stack" support?
// FIXME: What to do with IPOMANII-MIB::ipmEnvEmdConfigTempOffset.0 ?
echo ' IPOMANII-MIB ';
$emd_installed = snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdStatusEmdType.0', '-Oqv');

if ($emd_installed != 'disabled') {
    $descr = snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdConfigTempName.0', '-Oqv');
    $current = (snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdStatusTemperature.0', '-Oqv') / 10);
    $high_limit = snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdConfigTempHighSetPoint.0', '-Oqv');
    $low_limit = snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdConfigTempLowSetPoint.0', '-Oqv');

    if ($descr != '' && is_numeric($current) && $current > '0') {
        $current_oid = '.1.3.6.1.4.1.2468.1.4.2.1.5.1.1.2.0';
        $descr = trim(str_replace('"', '', $descr));

        discover_sensor($valid['sensor'], 'temperature', $device, $current_oid, '1', 'ipoman', $descr, '10', '1', $low_limit, null, null, $high_limit, $current);
    }
}
