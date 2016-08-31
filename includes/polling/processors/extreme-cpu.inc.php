<?php

//
// Hardcoded polling of CPU usage on Extreme devices due to the lack of multiplier for CPU usage.
//
// iso.3.6.1.4.1.1916.1.32.1.4.1.9.1 = STRING: "7.3"
if ($device['os'] == 'xos') {
    $usage = str_replace('"', "", snmp_get($device, '1.3.6.1.4.1.1916.1.32.1.4.1.9.1', '-OvQ', 'EXTREME-BASE-MIB'));

    if (is_numeric($usage)) {
        $proc = ($usage * 100);
//substr(snmp_get($device, '1.3.6.1.4.1.1916.1.32.1.4.1.9.1', '-Ovq', 'EXTREME-BASE-MIB'), 0, 2);
    }
}
