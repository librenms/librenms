<?php

$oid = '.1.3.6.1.4.1.7571.100.1.1.5.15.1.2.0';
$oids = snmp_walk($device, "$oid", '-OsqnU', 'SAF-IPRADIO');
d_echo($oids . "\n");

if (! empty($oids)) {
    echo 'SAF Voltage ';

    $divisor = 1;
    $type = 'saf';

    [,$current] = explode(' ', $oids);
    $index = $oid;
    $descr = 'System voltage';
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
