<?php

echo 'EXTREME-SOFTWARE-MONITOR-MIB';

$total = str_replace('"', "", snmp_get($device, "1.3.6.1.4.1.1916.1.32.2.2.1.2.1", '-OvQ'));
$avail = str_replace('"', "", snmp_get($device, "1.3.6.1.4.1.1916.1.32.2.2.1.3.1", '-OvQ'));

$mempool['total'] = ($total * 1024);
$mempool['free']  = ($avail * 1024);
$mempool['used']  = (($total - $avail) * 1024);
