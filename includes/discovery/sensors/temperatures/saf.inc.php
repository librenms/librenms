<?php

if ($device['os'] == 'saf') {
    $oid  = 'SAF-IPRADIO::sysTemperature';
    $oids = snmp_get($device, $oid, '-OsqnU', 'SAF-IPRADIO');
    d_echo($oids."\n");

    if (!empty($oids)) {
        echo 'SAF Temperature ';
    }

    $divisor = 1;
    $type    = 'saf';
    if (!empty($oids)) {
        list(,$current) = explode(' ', $oids);
        $index          = $oid;
        $descr          = 'System Temp';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}
