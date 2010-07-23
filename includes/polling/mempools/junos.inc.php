<?php

$oid = $mempool['mempool_index']; 

if($debug) {echo("JunOS Mempool");}

if(!is_array($mempool_cache['junos'])) {
  if($debug) {echo("caching");}
  $mempool_cache['junos'] = array();
  $mempool_cache['junos'] = snmpwalk_cache_multi_oid($device, "jnxOperatingBuffer", $mempool_cache['junos'], "JUNIPER-MIB" , "+".$config['install_dir']."/mibs/junos");
  $mempool_cache['junos'] = snmpwalk_cache_multi_oid($device, "jnxOperatingDRAMSize", $mempool_cache['junos'], "JUNIPER-MIB" , "+".$config['install_dir']."/mibs/junos");
  if($debug) {print_r($mempool_cache);}
}

$entry = $mempool_cache['junos'][$device[device_id]][$mempool[mempool_index]];

$perc = $entry['jnxOperatingBuffer'];
$mempool['total'] = $entry['jnxOperatingDRAMSize'];
$mempool['used'] = $entry['jnxOperatingDRAMSize'] / 100 * $perc;
$mempool['free'] = $entry['jnxOperatingDRAMSize'] - $mempool['used'];

?>
