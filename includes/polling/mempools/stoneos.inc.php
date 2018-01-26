<?php
echo 'Pool StoneOs memory';
$mempool['total'] = (snmp_get($device, 'HILLSTONE-SYSTEM-MIB::sysTotalMemory.0', '-OvQU') * 1024);
$mempool['used'] = (snmp_get($device, 'HILLSTONE-SYSTEM-MIB::sysCurMemory.0', '-OvQU') * 1024);
$mempool['free'] = ($mempool['total'] - $mempool['used']);
