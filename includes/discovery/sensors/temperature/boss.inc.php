<?php

$temps = snmp_walk($device, '.1.3.6.1.4.1.45.1.6.3.7.1.1.5.5', '-Osqn');

foreach (explode("\n", $temps) as $i => $t) {
    $t = explode(' ', $t);
    $oid = $t[0];
    $val = $t[1];
    // Sensors are reported as 2 * value
    $divisor = 2;
    $val = (cast_number($val) / $divisor);
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, zeropad($i + 1), 'avaya-ers', 'Unit ' . ($i + 1) . ' temperature', $divisor, 1, null, null, null, null, $val);
}
