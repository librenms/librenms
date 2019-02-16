<?php

echo 'Ruckus Wireless: ';
$perc = snmp_get($device, ".1.3.6.1.4.1.25053.1.15.1.1.1.15.14.0", '-OvQ');

if (is_numeric($perc)) {
    $mempool['perc'] = $perc;
    $mempool['used'] = $perc;
    $mempool['total'] = 100;
    $mempool['free'] = 100 - $perc;
}
