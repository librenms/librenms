<?php

// Simple hard-coded poller for ProxySG

echo 'ProxySG MemPool'.'\n';

$used = str_replace('"', "", snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyMemSysUsage.0", '-OUvQ'));
$total = str_replace('"', "", snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyMemAvailable.0", '-OUvQ'));
$free = ($total - $used);
$percent = ($used / $total * 100);

$mempool['used'] = ($used);
$mempool['free'] = ($free);
$mempool['total'] = (($used + $free));
