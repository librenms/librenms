<?php

echo 'APC/MGE UPS ';

$oids = snmp_walk($device, '.1.3.6.1.2.1.33.1.4.4.1.5', '-Osqn', 'UPS-MIB');
d_echo($oids."\n");

$oids = trim($oids);
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid,$descr) = explode(' ', $data, 2);
        $split_oid        = explode('.', $oid);
        $current_id       = $split_oid[(count($split_oid) - 1)];
        $current_oid      = ".1.3.6.1.2.1.33.1.4.4.1.5.$current_id";
        $current          = (snmp_get($device, $current_oid, '-O vq') / $divisor);
        $descr            = 'Percentage load'.(count(explode("\n", $oids)) == 1 ? '' : ' '.($current_id + 1));
        $index            = (100 + $current_id);

        discover_sensor($valid['sensor'], 'load', $device, $current_oid, $index, 'upsOutputPercentLoad', $descr, null, '1', null, null, null, null, $current);
    }
}
