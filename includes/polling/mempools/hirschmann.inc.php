<?php

$mem_allocated = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmMemoryAllocated.0', '-OvQU');
$mem_free = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmMemoryFree.0', '-OvQU');
$perc = $mem_allocated / ($mem_allocated + $mem_free) * 100;

$mempool['perc'] = $perc;
$mempool['total'] = ($mem_allocated + $mem_free);
$mempool['used'] = $mem_allocated;
$mempool['free'] = $mem_free;

echo '(U: '.$mempool['used'].' T: '.$mempool['total'].' F: '.$mempool['free'].') ';
