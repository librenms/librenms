<?php

$cambium_type = snmp_get($device, 'sysDescr.0', '-Oqv', '');
$divisor = 1;
if (strstr($cambium_type, 'BHUL450')) {
    $masterSlaveMode = snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    if ($masterSlaveMode == 'timingMaster') {
        $oid = '.1.3.6.1.4.1.17713.21.1.2.3.2';
        $mib = 'WHISP-APS-MIB';
    } else {
        $oid = '.1.3.6.1.4.1.161.19.3.2.2.21.0';
        $mib = 'WHISP-SM-MIB';
    }
} elseif (strstr($cambium_type, 'BHUL') || strstr($cambium_type, 'BH')) {
    $masterSlaveMode = snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    if ($masterSlaveMode == 'timingMaster') {
        $oid = '.1.3.6.1.4.1.17713.21.1.2.3.2';
        $mib = 'WHISP-APS-MIB';
    } else {
        $oid = '.1.3.6.1.4.1.161.19.3.2.2.21.0';
        $mib = 'WHISP-BOX-MIBV2-MIB';
    }
} else {
    $oid = '.1.3.6.1.4.1.161.19.3.2.2.21.0';
    $mib = 'WHISP-BOX-MIBV2-MIB';
}

$oids = trim(str_replace('"', '', snmp_get($device, "$oid", '-OsqnU', $mib)));
d_echo($oids . "\n");

if (! empty($oids)) {
    echo 'Canopy Signal ';
}

$type = 'canopy';
if (! empty($oids)) {
    [,$current] = explode(' ', $oids);
    $current = $current / $divisor;
    $index = $oid;
    $descr = 'Signal';
    discover_sensor($valid['sensor'], 'signal', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
