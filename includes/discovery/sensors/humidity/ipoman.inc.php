<?php

// FIXME: EMD "stack" support
// FIXME: What to do with IPOMANII-MIB::ipmEnvEmdConfigHumiOffset.0 ?
echo ' IPOMANII-MIB ';
$emd_installed = snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdStatusEmdType.0', '-Oqv');

if ($emd_installed == 'eMD-HT') {
    $descr = snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdConfigHumiName.0', '-Oqv');
    $current = (snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdStatusHumidity.0', '-Oqv') / 10);
    $high_limit = snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdConfigHumiHighSetPoint.0', '-Oqv');
    $low_limit = snmp_get($device, 'IPOMANII-MIB::ipmEnvEmdConfigHumiLowSetPoint.0', '-Oqv');

    if ($descr != '' && is_numeric($current) && $current > '0') {
        $current_oid = '.1.3.6.1.4.1.2468.1.4.2.1.5.1.1.3.0';
        $descr = trim(str_replace('"', '', $descr));

        discover_sensor($valid['sensor'], 'humidity', $device, $current_oid, '1', 'ipoman', $descr, '10', '1', $low_limit, null, null, $high_limit, $current);
    }
}
