<?php

if ($device['os'] == 'netbotz') {
    $oids = snmp_walk($device, '.1.3.6.1.4.1.5528.100.4.1.1.1.4', '-Osqn', '');
    d_echo($oids."\n");

    $oids = trim($oids);
    if ($oids) {
        echo 'NetBotz ';
        foreach (explode("\n", $oids) as $data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $temperature_id   = $split_oid[(count($split_oid) - 1)];
            $temperature_oid  = ".1.3.6.1.4.1.5528.100.4.1.1.1.8.$temperature_id";
            $temperature      = snmp_get($device, $temperature_oid, '-Ovq');
            $descr            = str_replace('"', '', $descr);
            $descr            = preg_replace('/Temperature  /', '', $descr);
            $descr            = trim($descr);
            if ($temperature != '0' && $temperature <= '1000') {
                discover_sensor($valid['sensor'], 'temperature', $device,
                    $temperature_oid, $temperature_id, 'netbotz',
                    $descr, '1', '1', null, null, null, null, $temperature);
            }
        }
    }
}
