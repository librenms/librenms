<?php

## JUNOSe Processors
if($device['os'] == "junos")
{
  echo("JUNOSe : ");
  $processors_array = snmpwalk_cache_multi_oid($device, "jnxOperatingCPU", $processors_array, "JUNIPER-MIB" , "+".$config['install_dir']."/mibs/junos");
  $processors_array = snmpwalk_cache_multi_oid($device, "jnxOperatingDRAMSize", $processors_array, "JUNIPER-MIB" , "+".$config['install_dir']."/mibs/junos");
  $processors_array = snmpwalk_cache_multi_oid($device, "jnxOperatingDescr", $processors_array, "JUNIPER-MIB" , "+".$config['install_dir']."/mibs/junos");
  if($debug) { print_r($processors_array); }
  foreach($processors_array[$device['device_id']] as $index => $entry) {

   if($entry['jnxOperatingDRAMSize'] && !strpos($entry['jnxOperatingDescr'], "sensor") && !strstr($entry['jnxOperatingDescr'], "fan")) {
     echo($index . " " . $entry['jnxOperatingDescr'] . " -> " . $entry['jnxOperatingCPU'] . " -> " . $entry['jnxOperatingDRAMSize'] . "\n");

     $usage_oid = ".1.3.6.1.4.1.2636.3.1.13.1.8." . $index;
     $descr = $entry['jnxOperatingDescr'];
     $usage = $entry['jnxOperatingCPU'];
     if(!strstr($descr, "No") && !strstr($usage, "No") && $descr != "" ) {
       discover_processor($valid_processor, $device, $usage_oid, $index, "junos", $descr, "1", $usage, NULL, NULL);
     }
   }
  } 
} ## End JUNOS Processors

  unset ($processors_array);

?>
