<?php

$main_oid = '.1.3.6.1.4.1.789.1.21.1.2.1';
$oids = snmp_walk($device, $main_oid . '.25', '-Osqn');
d_echo($oids . "\n");
$oids = trim($oids);
if ($oids) {
    echo 'NetApp ';
    foreach (explode("\n", $oids) as $data) {
        [$oid,$descr] = explode(' ', $data, 2);
        $split_oid = explode('.', $oid);
        $temperature_id = $split_oid[count($split_oid) - 1];
        $x = 1;
        preg_match_all('/([0-9]+C)+/', $descr, $temps);
        preg_match_all('/([0-9]+C)+/', snmp_get($device, $main_oid . '.26.' . $temperature_id, '-Ovq'), $over_fail);
        preg_match_all('/([0-9]+C)+/', snmp_get($device, $main_oid . '.27.' . $temperature_id, '-Ovq'), $over_warn);
        preg_match_all('/([0-9]+C)+/', snmp_get($device, $main_oid . '.28.' . $temperature_id, '-Ovq'), $under_fail);
        preg_match_all('/([0-9]+C)+/', snmp_get($device, $main_oid . '.29.' . $temperature_id, '-Ovq'), $under_warn);
        $x = 0;
        foreach ($temps[0] as $temperature) {
            $low_limit = str_replace('C', '', $under_fail[0][$x]);
            $low_warn_limit = str_replace('C', '', $under_warn[0][$x]);
            $warn_limit = str_replace('C', '', $over_warn[0][$x]);
            $high_limit = str_replace('C', '', $over_fail[0][$x]);
            $temperature_oid = $main_oid . '.25.' . $temperature_id;
            $temp_id = $temperature_id . '.' . $x;
            $descr = 'Temp Sensor';
            $x++;
            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temp_id, 'netapp', $descr, '1', '1', $low_limit, $low_warn_limit, $warn_limit, $high_limit, $temperature);
        }
    }
}
