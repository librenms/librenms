<?php

  echo 'ARUBAOS-MEMORY-POOL: ';

  $memory_pool = snmp_get_multi_oid($device, ['sysXMemorySize.1', 'sysXMemoryUsed.1', 'sysXMemoryFree.1'], '-OQUs', 'WLSX-SWITCH-MIB');

  $mempool['total'] = $memory_pool['sysXMemorySize.1'] * 1024;
  $mempool['used']  = $memory_pool['sysXMemoryUsed.1'] * 1024;
  $mempool['free']  = $memory_pool['sysXMemoryFree.1'] * 1024;
  $mempool['perc']  = ($mempool['used'] / $mempool['total'] * 100);
