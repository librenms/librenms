<?php

  $oid = $mempool['mempool_index']; 

  $pool_cmd  = $config['snmpget'] . " -m CISCO-MEMORY-POOL-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
  $pool_cmd .= " ciscoMemoryPoolUsed.$oid ciscoMemoryPoolFree.$oid ciscoMemoryPoolLargestFree.$oid";
  $pool_cmd .= " | cut -f 1 -d ' '";

  echo("$pool_cmd");

  $pool = shell_exec($pool_cmd);

  list($mempool['used'], $mempool['free'], $mempool['largestfree']) = explode("\n", $pool);
  $mempool['total'] = $mempool['used'] + $mempool['free'];

?>
