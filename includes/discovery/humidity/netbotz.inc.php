<?php

if ($device['os'] == 'netbotz') {
    $oids = snmp_walk($device, '.1.3.6.1.4.1.5528.100.4.1.2.1.4', '-Osqn', '');
    d_echo($oids."\n");

    $oids = trim($oids);
    if ($oids) {
        echo 'Netbotz ';
        foreach (explode("\n", $oids) as $data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $humidity_id      = $split_oid[(count($split_oid) - 1)];
            // tempHumidSensorHumidValue
            $humidity_oid = '.1.3.6.1.4.1.5528.100.4.1.2.1.8.'.$humidity_id;
            $humidity     = snmp_get($device, "$humidity_oid", '-Ovq', '');
            $descr        = str_replace('"', '', $descr);
            $descr        = trim($descr);
            if ($humidity >= 0) {
                discover_sensor($valid['sensor'], 'humidity', $device,
                    $humidity_oid, $humidity_id, 'netbotz',
                    $descr, '1', '1', null, null, null, null, $humidity);
            }
        }

        unset($data);
    }

    unset($oids);
}
