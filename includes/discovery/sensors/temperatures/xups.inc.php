<?php

if ($device['os'] == 'powerware') {
    // XUPS-MIB::xupsEnvAmbientTemp.0 = INTEGER: 52
    // XUPS-MIB::xupsEnvAmbientLowerLimit.0 = INTEGER: 0
    // XUPS-MIB::xupsEnvAmbientUpperLimit.0 = INTEGER: 70
    $oids = snmp_walk($device, 'xupsEnvAmbientTemp', '-Osqn', 'XUPS-MIB');
    d_echo($oids."\n");

    $oids = trim($oids);
    if ($oids) {
        echo 'Powerware Ambient Temperature ';
    }

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $temperature_id   = $split_oid[(count($split_oid) - 1)];
            $temperature_oid  = ".1.3.6.1.4.1.534.1.6.1.$temperature_id";
            $lowlimit         = snmp_get($device, "upsEnvAmbientLowerLimit.$temperature_id", '-Ovq', 'XUPS-MIB');
            $highlimit        = snmp_get($device, "upsEnvAmbientUpperLimit.$temperature_id", '-Ovq', 'XUPS-MIB');
            $temperature      = snmp_get($device, $temperature_oid, '-Ovq');
            $descr            = 'Ambient'.(count(explode("\n", $oids)) == 1 ? '' : ' '.($temperature_id + 1));

            discover_sensor($valid['sensor'], 'temperature', $device,
                $temperature_oid, '1.6.1.'.$temperature_id, 'powerware',
                $descr, '1', '1', $lowlimit, null, null, $highlimit, $temperature);
        }
    }
}
