<?php

// Code below was borrowed from 'powerconnect-cpu.inc.php' 


//--------------------------------------------------------------------//
// Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.1.0 = INTEGER: 23127
// Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.2.0 = INTEGER: 262144
// Simple hard-coded poller for Dell Powerconnect (tested on 6248P)
// Yes, it really can be this simple.
// Pity there's no matching MIB to be found.

$get_series = explode('.', snmp_get($device, 'mib-2.1.2.0', '-Onvsbq', 'F10-PRODUCTS-MIB', 'dnos'), 2); // Get series From MIB
$series = $get_series[0];
if ($series == 'f10SSeriesProducts') {
    $mempool['total'] = snmp_get($device, 'chSysProcessorMemSize.1', '-OvQU', 'F10-S-SERIES-CHASSIS-MIB');
    $mempool['used']  = $mempool['total'] * (snmp_get($device, 'chStackUnitMemUsageUtil.1', '-OvQU', 'F10-S-SERIES-CHASSIS-MIB')/ 100);
    $mempool['free']  = ($mempool['total'] - $mempool['used']);
} else {
    $mempool['total'] = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.2.0', '-OvQ');
    $mempool['free']  = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.1.0', '-OvQ');
    $mempool['used']  = ($mempool['total'] - $mempool['free']);
}
