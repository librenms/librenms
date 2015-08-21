<?php

if ($device['os'] == 'siklu') {
    $oid  = 'rbSysTemperature.0';
    $oids = snmp_get($device, "$oid", '-OsqnU', 'RADIO-BRIDGE-MIB');
    d_echo($oids."\n");

    if (!empty($oids)) {
        echo 'Siklu Temperature ';
    }

    $divisor = 1;
    $type    = 'siklu';
    if (!empty($oids)) {
        list(,$current) = explode(' ', $oids);
        $index          = $oid;
        $descr          = 'System Temp';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}
