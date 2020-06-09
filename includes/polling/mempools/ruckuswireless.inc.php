<?php

echo 'Ruckus Wireless';

$perc = snmp_get($device, "ruckusZDSystemMemoryUtil.0", '-OvQ', 'RUCKUS-ZD-SYSTEM-MIB');

if (is_numeric($perc)) {
    $mempool['perc'] = $perc;
    $mempool['used'] = $perc;
    $mempool['total'] = 100;
    $mempool['free'] = 100 - $perc;
}
