<?php

//OACOMMON-MIB::netSpireDevicestorageUsed
$usage = str_replace('"', "", snmp_get($device, "1.3.6.1.4.1.1732.2.1.8.0", "-OQv"));

if (is_numeric($usage)) {
    $mempool['total'] = 100;
    $mempool['used'] = $usage;
    $mempool['free'] = 100 - $usage;
}
