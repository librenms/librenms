<?php

echo 'Benu';
$memdata = snmp_get_multi($device, ['bSysTotalMem.0', 'bSysMemUsed.0'], '-OQUs', 'BENU-HOST-MIB');
$mempool['total'] = $memdata[0]['bSysTotalMem'];
$mempool['used']  = $memdata[0]['bSysMemUsed'];
$mempool['free']  = ($mempool['total'] - $mempool['used']);
$mempool['perc']  = ($mempool['used'] / $mempool['total']) * 100;
