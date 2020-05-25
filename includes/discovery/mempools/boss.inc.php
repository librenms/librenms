<?php

$OID = '.1.3.6.1.4.1.45.1.6.3.8.1.1.12';

if ($device['os'] == 'boss') {
    $mem = snmp_walk($device, $OID, '-Osqn');

    echo "$mem\n";

    foreach (explode("\n", $mem) as $i => $t) {
        $t   = explode(' ', $t);
        $oid = str_replace($OID, '', $t[0]);
        discover_mempool($valid_mempool, $device, $oid, 'avaya-ers', 'Unit '.($i + 1).' memory', '1', null, null);
    }
}
