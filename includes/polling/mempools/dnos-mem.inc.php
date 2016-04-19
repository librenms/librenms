<?php

// Code below was borrowed from 'powerconnect-cpu.inc.php' 


//--------------------------------------------------------------------//
// Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.1.0 = INTEGER: 23127
// Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.2.0 = INTEGER: 262144
// Simple hard-coded poller for Dell Powerconnect (tested on 6248P)
// Yes, it really can be this simple.
// Pity there's no matching MIB to be found.
$mempool['total'] = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.2.0', '-OvQ');
$mempool['free']  = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.1.0', '-OvQ');
$mempool['used']  = ($mempool['total'] - $mempool['free']);
