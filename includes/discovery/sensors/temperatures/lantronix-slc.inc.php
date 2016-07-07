<?php

if ($device['os'] == 'lantronix-slc') {
    echo("Lantronix SLC");

    // Chassis temperature
    $high_limit         = 50;
    $high_warn_limit    = 45;
    $low_warn_limit     = 5;
    $low_limit          = 1;

    $descr   = "System Temperature:";
    $valueoid     = ".1.3.6.1.4.1.244.1.1.6.25.0"; // LANTRONIX-SLC-MIB::slcSystemInternalTemp.0
    $value   = snmp_get($device, $valueoid, '-Oqv');

    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'temperature', $device, $valueoid, 1, 'lantronix-slc', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
    }
}
