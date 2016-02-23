<?php

// Simple hard-coded poller for Brocade Ironware Dynamic Memory (old style)
// Yes, it really can be this simple.
$mempool['total'] = snmp_get($device, 'snAgGblDynMemTotal.0', '-OvQ', 'FOUNDRY-SN-AGENT-MIB');
$mempool['free']  = snmp_get($device, 'snAgGblDynMemFree.0', '-OvQ', 'FOUNDRY-SN-AGENT-MIB');
$mempool['used']  = ($mempool['total'] - $mempool['free']);
