<?php

$index = $mempool['mempool_index'];

$total = (snmp_get($device, ".1.3.6.1.4.1.45.1.6.3.8.1.1.12$index", '-Oqv') * 1048576);
$avail = (snmp_get($device, ".1.3.6.1.4.1.45.1.6.3.8.1.1.13$index", '-Oqv') * 1048576);

$mempool['total'] = $total;
$mempool['free']  = $avail;
$mempool['used']  = ($total - $avail);
