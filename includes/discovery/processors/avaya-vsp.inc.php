<?php

if ($device['os'] == 'avaya-vsp') {
        $procs = snmp_walk($device, '.1.3.6.1.4.1.2272.1.85.10.1.1.2.1', '-Osqn');

        foreach (explode("\n", $procs) as $i => $t) {
            $t   = explode(' ', $t);
            $oid = $t[0];
            $val = $t[1];
            discover_processor($valid['processor'], $device, $oid, zeropad($i +1), 'avaya-vsp', 'Unit '.($i + 1).' processor', '1', $val, $i, null);
        }
}
