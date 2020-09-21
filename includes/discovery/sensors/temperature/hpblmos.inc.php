<?php

$sensor_type = 'hpblmos_temps';
$temps_oid = '.1.3.6.1.4.1.232.22.2.3.1.2.1.5';
$sensor_value_oid = '.1.3.6.1.4.1.232.22.2.3.1.2.1.6.';

$temps = trim(snmp_walk($device, $temps_oid, '-Osqn'));

foreach (explode("\n", $temps) as $temp) {
    $temp = trim($temp);
    if ($temp) {
        [$oid, $descr] = explode(' ', $temp, 2);
        if ($descr != '') {
            $split_oid = explode('.', $oid);
            $current_id = $split_oid[(count($split_oid) - 1)];
            $current_oid = $sensor_value_oid . $current_id;
            $value = snmp_get($device, $current_oid, '-Oqve');
            if ($value > 0) {
                discover_sensor($valid['sensor'], 'temperature', $device, $current_oid, $current_id, $sensor_type, $descr, 1, 1, null, null, null, null, $value);
            }
        }
    }
}
