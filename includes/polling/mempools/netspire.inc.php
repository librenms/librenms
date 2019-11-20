<?php

$usage = str_replace('"', "", snmp_get($device, "netSpireDeviceStorageUsed.0", "-OQv", 'OACOMMON-MIB'));

if (is_numeric($usage)) {
    $mempool['total'] = 100;
    $mempool['used'] = $usage;
    $mempool['free'] = 100 - $usage;
}
