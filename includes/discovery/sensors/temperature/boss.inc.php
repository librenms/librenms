<?php

use Illuminate\Support\Str;
use LibreNMS\Util\Number;

$temps = snmp_walk($device, '.1.3.6.1.4.1.45.1.6.3.7.1.1.5.5', '-Osqn');
if (empty($temps)) {
    return;
}

foreach (explode("\n", $temps) as $i => $t) {
    $t = explode(' ', $t);
    $oid = $t[0];
    $val = $t[1];
    // Sensors are reported as 2 * value
    $divisor = 2;
    $val = (Number::cast($val) / $divisor);
    discover_sensor(null, 'temperature', $device, $oid, Str::padLeft($i + 1, 2, '0'), 'avaya-ers', 'Unit ' . ($i + 1) . ' temperature', $divisor, 1, null, null, null, null, $val);
}
