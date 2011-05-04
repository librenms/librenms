<?php

## JUNOSe Processors
if ($device['os'] == "junose")
{
  echo("JUNOSe : ");
  $processors_array = snmpwalk_cache_double_oid($device, "juniSystemModule", $processors_array, "Juniper-System-MIB" , $config['install_dir']."/mibs/junose");
  if ($debug) { print_r($processors_array); }

  foreach ($processors_array as $index => $entry)
  {
    if ($entry['juniSystemModuleCpuUtilPct'] && $entry['juniSystemModuleCpuUtilPct'] != "-1")
    {
      $entPhysicalIndex = $entry['juniSystemModulePhysicalIndex'];
      $usage_oid = ".1.3.6.1.4.1.4874.2.2.2.1.3.5.1.3." . $index;
      $descr_oid = ".1.3.6.1.4.1.4874.2.2.2.1.3.5.1.6." . $index;
      $descr = $entry['juniSystemModuleDescr'];
      $usage = $entry['juniSystemModuleCpuFiveMinAvgPct'];

      if (!strstr($descr, "No") && !strstr($usage, "No") && $descr != "")
      {
        discover_processor($valid['processor'], $device, $usage_oid, $index, "junose", $descr, "1", $usage, $entPhysicalIndex, NULL);
      }
    }
  }
} ## End JUNOSe Processors

unset ($processors_array);

?>
