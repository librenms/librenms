<?php

/*
/ BENU-HOST-MIB:bSysMemFree.0	228418196	Gauge
/ BENU-HOST-MIB:bSysMemUsed.0	299799016	Gauge
/ BENU-HOST-MIB:bSysMemFree.0	228418416	Gauge
*/


$mempool['used']  = snmp_get($device, 'bSysMemUsed.0', '-OvQ', 'BENU-HOST-MIB');
$mempool['total'] = snmp_get($device, 'bSysTotalMem.0', '-OvQ', 'BENU-HOST-MIB');
$mempool['free']  = ($mempool['total'] - $mempool['used']);
$mempool['perc']  = ($mempool['used'] / $mempool['total']) * 100;
