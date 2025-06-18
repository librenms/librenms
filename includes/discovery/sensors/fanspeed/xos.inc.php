<?php

echo ' EXTREME-SYSTEM-MIB ';

// Fan Speed
$oid = '.1.3.6.1.4.1.1916.1.1.1.9.1.4';
$oids = snmpwalk_cache_multi_oid($device, $oid, [], 'EXTREME-SYSTEM-MIB');

foreach ($oids as $index => $entry) {
    // fix index to a proper int
    if (preg_match('/^.*\.([^\.]*)$/', $index, $matches)) {
        $index = $matches[1];
    }

    // substract 100 from index to start from 1 instead of 101
    $modindex = ($index - 100);
    $oid = ".1.3.6.1.4.1.1916.1.1.1.9.1.4.$index";
    $value = snmp_get($device, $oid, '-Oqv', 'EXTREME-SYSTEM-MIB');
    if (is_numeric($value)) {
        $descr = "Fan Speed $modindex";
        discover_sensor(null, 'fanspeed', $device, $oid, $index, 'extreme-fanspeed', $descr, current: $value);
    }
}
