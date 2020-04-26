<?php

// Simple hard-coded poller for Fortinet Fortigate
// Yes, it really can be this simple.
d_echo('fortimail');
if ($mempool['mempool_index'] == 0) {
    $perc = snmp_get($device, ".1.3.6.1.4.1.12356.105.1.7.0", '-OvQ');
    if (is_numeric($perc)) {
        $mempool['perc'] = $perc;
        $mempool['used'] = $perc;
        $mempool['total'] = 100;
        $mempool['free'] = 100 - $perc;
    }
}

if ($mempool['mempool_index'] == 1) {
    $perc = snmp_get($device, ".1.3.6.1.4.1.12356.105.1.8.0", '-OvQ');
    if (is_numeric($perc)) {
        $mempool['perc'] = $perc;
        $mempool['used'] = $perc;
        $mempool['total'] = 100;
        $mempool['free'] = 100 - $perc;
    }
}

if ($mempool['mempool_index'] == 2) {
    $perc = snmp_get($device, ".1.3.6.1.4.1.12356.105.1.9.0", '-OvQ');
    if (is_numeric($perc)) {
        $mempool['perc'] = $perc;
        $mempool['used'] = $perc;
        $mempool['total'] = 100;
        $mempool['free'] = 100 - $perc;
    }
}