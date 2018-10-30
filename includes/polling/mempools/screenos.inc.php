<?php

// Simple hard-coded poller for Juniper ScreenOS
// Yes, it really can be this simple.
$mempool['used']  = snmp_get($device, '.1.3.6.1.4.1.3224.16.2.1.0', '-OvQ');
$mempool['free'] = snmp_get($device, '.1.3.6.1.4.1.3224.16.2.2.0', '-OvQ');
$mempool['total']  = ($mempool['free'] + $mempool['used']);
