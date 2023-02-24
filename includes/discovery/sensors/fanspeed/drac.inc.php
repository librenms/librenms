<?php

$oids = snmp_walk($device, 'coolingDevicechassisIndex.1', '-OsqnU', 'IDRAC-MIB-SMIv2');
d_echo($oids . "\n");

$oids = trim($oids);
if ($oids) {
    echo 'Dell iDRAC';
}

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$kind] = explode(' ', $data);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        $fan_oid = ".1.3.6.1.4.1.674.10892.5.4.700.12.1.6.1.$index";
        $descr_oid = "coolingDeviceLocationName.1.$index";
        $limit_oid = "coolingDeviceLowerCriticalThreshold.1.$index";
        $descr = trim(snmp_get($device, $descr_oid, '-Oqv', 'IDRAC-MIB-SMIv2'), '"');
        $descr = preg_replace('/(Board | MOD )/', '', $descr);
        $current = snmp_get($device, $fan_oid, '-Oqv', 'IDRAC-MIB-SMIv2');
        $low_limit = snmp_get($device, $limit_oid, '-Oqv', 'IDRAC-MIB-SMIv2');
        $divisor = '1';
        discover_sensor($valid['sensor'], 'fanspeed', $device, $fan_oid, $index, 'drac', $descr, $divisor, '1', $low_limit, null, null, null, $current);
    }
}
