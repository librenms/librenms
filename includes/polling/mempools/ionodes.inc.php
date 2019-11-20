<?php

echo 'IONODES: ';

$perc = snmp_get($device, 'ionSysMemUsage.0', '-OvUQ', 'IONODES-IONSERIES-MIB');

if (is_numeric($perc)) {
    $mempool['perc'] = $perc;
    $mempool['used'] = $perc;
    $mempool['total'] = 100;
    $mempool['free'] = 100 - $perc;
}
