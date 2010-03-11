<?php

if($device['os'] == "ios" || $device['os_type'] == "ios") {

  echo("CISCO-ENHANCED-MEMORY-POOL: ");

  $array = snmpwalk_cache_multi_oid($device, "cempMemPoolEntry", array(), "CISCO-ENHANCED-MEMPOOL-MIB");

  if(is_array($array)) {
    foreach($array[$device[device_id]] as $index => $entry) {
      if(is_numeric($entry['cempMemPoolUsed']) && $entry['cempMemPoolValid'] == "true") {
       list($entPhysicalIndex) = explode(".", $index);
       $entPhysicalDescr = snmp_get($device, "entPhysicalDescr.".$entPhysicalIndex, "-Oqv", "ENTITY-MIB");       
       $descr = $entPhysicalDescr." - ".$entry['cempMemPoolName'];
       discover_mempool($valid_mempool, $device, $index, "cemp", $descr, "1", $entPhysicalIndex, NULL);
      }
    }
  }
}

?>
