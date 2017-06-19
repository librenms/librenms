<?php

$mempool['used']  = snmp_get($device, 'bSysMemUsed.0', '-OvQ', 'BENU-HOST-MIB');
$mempool['total'] = snmp_get($device, 'bSysTotalMem.0', '-OvQ', 'BENU-HOST-MIB');
$mempool['free']  = ($mempool['total'] - $mempool['used']);
$mempool['perc']  = ($mempool['used'] / $mempool['total']) * 100;
