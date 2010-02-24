<?php

if($device['os'] == "ios" || $device['os_type'] == "ios") {

  echo("OLD-CISCO-MEMORY-POOL: ");

  $cmp_oids = array('ciscoMemoryPool');

  foreach ($cmp_oids as $oid) { $cmp_array = snmp_cache_oid($oid, $device, array(), "CISCO-MEMORY-POOL-MIB"); }

  if(is_array($cmp_array)) {
    foreach($cmp_array[$device[device_id]] as $index => $cmp) {
      if(is_numeric($cmp['ciscoMemoryPoolUsed']) && is_numeric($index)) {
        discover_mempool($valid_mempool, $device, $index, "cmp", $cmp['ciscoMemoryPoolName'], "1", NULL, NULL);
      }
    }
  }
}

?>
