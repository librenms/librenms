<?php

$mib = 'SAF-IPRADIO::sysTemperature';
$oid = '.1.3.6.1.4.1.7571.100.1.1.5.1.1.1.5';
$oids = snmp_get($device, $mib, '-OsqnU', 'SAF-IPRADIO');
d_echo($oids . "\n");

if (! empty($oids)) {
    echo 'SAF Temperature ';

    $divisor = 1;
    $type = 'saf';

    [,$current] = explode(' ', $oids);
    $index = $oid;
    $descr = 'System Temp';
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
