<?php

$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.2.2.3.0', '-OsqnUt', '');
d_echo($oids."\n");
if ($oids) {
    echo ' APC Runtime ';
    list($oid,$current) = explode(' ', $oids);
    $divisor            = 6000;
    $type               = 'apc';
    $index              = 'upsAdvBatteryRunTimeRemaining.0';
    $descr              = 'Runtime';
    $low_limit          = 5;
    $low_limit_warn     = 10;
    $warn_limit         = 2000;
    $high_limit         = 3000;
    discover_sensor($valid['sensor'], 'runtime', $device, $oid, $index, $type, $descr, $divisor, '1', $low_limit, $low_limit_warn, $warn_limit, $high_limit, $current);
}
