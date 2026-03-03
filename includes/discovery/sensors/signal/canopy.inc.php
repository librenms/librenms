<?php

$cambium_type = SnmpQuery::get('sysDescr.0')->value();
$divisor = 1;
if (strstr((string) $cambium_type, 'BHUL450')) {
    $masterSlaveMode = SnmpQuery::get('WHISP-BOX-MIBV2-MIB::bhTimingMode.0')->value();
    if ($masterSlaveMode == 'timingMaster') {
        $oid = '.1.3.6.1.4.1.17713.21.1.2.3.2';
        $mib = 'WHISP-APS-MIB';
    } else {
        $oid = '.1.3.6.1.4.1.161.19.3.2.2.21.0';
        $mib = 'WHISP-SM-MIB';
    }
} elseif (strstr((string) $cambium_type, 'BHUL') || strstr((string) $cambium_type, 'BH')) {
    $masterSlaveMode = SnmpQuery::get('WHISP-BOX-MIBV2-MIB::bhTimingMode.0')->value();
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
    $current /= $divisor;
    $index = $oid;
    $descr = 'Signal';
    discover_sensor(null, \LibreNMS\Enum\Sensor::Signal, $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
