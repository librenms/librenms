<?php

echo 'Maipu MemPool';

$memory = snmp_get_multi_oid($device, ['memoryTotalBytes.0', 'numBytesAlloc.0', 'numBytesFree.0'], '-OvQ', 'MPIOS-MIB');

$mempool['total'] = $memory['memoryTotalBytes.0'];
$mempool['used'] = $memory['numBytesAlloc.0'];
$mempool['free'] = $memory['numBytesFree.0'];
