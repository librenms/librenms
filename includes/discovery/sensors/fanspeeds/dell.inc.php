<?php

if ($device['os'] == 'linux') {
    $oids = snmp_walk($device, 'coolingDevicechassisIndex.1', '-OsqnU', 'MIB-Dell-10892');
    d_echo($oids."\n");

    $oids = trim($oids);
    if ($oids) {
        echo 'Dell ';
    }

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$kind) = explode(' ', $data);
            $split_oid       = explode('.', $oid);
            $index           = $split_oid[(count($split_oid) - 1)];
            $fan_oid         = ".1.3.6.1.4.1.674.10892.1.700.12.1.6.1.$index";
            $descr_oid       = "coolingDeviceLocationName.1.$index";
            $limit_oid       = "coolingDeviceLowerCriticalThreshold.1.$index";
            $descr           = trim(snmp_get($device, $descr_oid, '-Oqv', 'MIB-Dell-10892'), '"');
            $descr           = preg_replace('/(Board | MOD )/', '', $descr);
            $current         = snmp_get($device, $fan_oid, '-Oqv', 'MIB-Dell-10892');
            $low_limit       = snmp_get($device, $limit_oid, '-Oqv', 'MIB-Dell-10892');
            $divisor         = '1';
            discover_sensor($valid['sensor'], 'fanspeed', $device, $fan_oid, $index, 'dell', $descr, $divisor, '1', $low_limit, null, null, null, $current);
        }
    }
}
