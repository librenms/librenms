<?php

echo ' IPOMANII-MIB ';

$oids_out = [];

d_echo('outletStatusKwatt ');
$oids_out = snmpwalk_cache_multi_oid($device, 'outletStatusKwatt', $oids_out, 'IPOMANII-MIB');

if (is_array($oids_out)) {
    foreach ($oids_out as $index => $entry) {
        $power_consumed_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.2.3.1.4.' . $index;
        $divisor = 1000;
        $descr = (trim($pre_cache['ipoman']['out'][$index]['outletConfigDesc'], '"') != '' ? trim($pre_cache['ipoman']['out'][$index]['outletConfigDesc'], '"') : "Output $index");
        discover_sensor($valid['sensor'], 'power_consumed', $device, $power_consumed_oid, $power_consumed_oid, 'ipoman', $descr, $divisor, 1, 0, null, null, 0);
    }
}
