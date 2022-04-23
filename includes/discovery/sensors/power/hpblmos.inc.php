<?php

$sensor_type = 'hpblmos_psu_usage';
$psu_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.16';
$psu_usage_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.10.';
$psu_max_usage_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.9.';

$psus = trim(snmp_walk($device, $psu_oid, '-Osqn'));

foreach (explode("\n", $psus) as $psu) {
    $psu = trim($psu);
    if ($psu) {
        [$oid, $presence] = explode(' ', $psu, 2);
        if ($presence != 2) {
            $split_oid = explode('.', $oid);
            $current_id = $split_oid[(count($split_oid) - 1)];
            $current_oid = $psu_usage_oid . $current_id;
            $psu_max_oid = $psu_max_usage_oid . $current_id;
            $descr = 'PSU ' . $current_id . ' output';
            $value = snmp_get($device, $current_oid, '-Oqv');
            $max_value = snmp_get($device, $psu_max_oid, '-Oqv');
            discover_sensor($valid['sensor'], 'power', $device, $current_oid, $current_id, $sensor_type, $descr, 1, 1, null, null, null, $max_value, $value);
        }
    }
}
