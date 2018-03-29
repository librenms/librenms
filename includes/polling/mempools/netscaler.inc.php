<?php

// NS-ROOT-MIB::memSizeMB.0 = INTEGER: 815
// NS-ROOT-MIB::resMemUsage.0 = Gauge32: 29
// .1.3.6.1.4.1.5951.4.1.1.41.4.0 = INTEGER: 815
// .1.3.6.1.4.1.5951.4.1.1.41.2.0 = Gauge32: 29
// Simple hard-coded poller for Netscaler
// Yes, it really can be this simple.
$mempool['total'] = (snmp_get($device, '.1.3.6.1.4.1.5951.4.1.1.41.4.0', '-OvQ') * 1047552);
$mempool['perc']  = snmp_get($device, '.1.3.6.1.4.1.5951.4.1.1.41.2.0', '-OvQ');
$mempool['used']  = ($mempool['total'] / 100 * $mempool['perc']);
$mempool['free']  = ($mempool['total'] - $mempool['used']);
