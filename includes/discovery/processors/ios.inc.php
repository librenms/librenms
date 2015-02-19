<?php

if ($device['os_group'] == "cisco" || $device['os'] == "acsw")
{
  echo("CISCO-PROCESS-MIB: ");
  $processors_array = snmpwalk_cache_oid($device, "cpmCPU", NULL, "CISCO-PROCESS-MIB");
  if ($debug) { print_r($processors_array); }

  foreach ($processors_array as $index => $entry)
  {
    if (is_numeric($entry['cpmCPUTotal5minRev']) || is_numeric($entry['cpmCPUTotal5min']))
    {
      $entPhysicalIndex = $entry['cpmCPUTotalPhysicalIndex'];

      if (isset($entry['cpmCPUTotal5minRev']))
      {
        $usage_oid = ".1.3.6.1.4.1.9.9.109.1.1.1.1.8." . $index;
        $usage = $entry['cpmCPUTotal5minRev'];
      } elseif (isset($entry['cpmCPUTotal5min'])) {
        $usage_oid = ".1.3.6.1.4.1.9.9.109.1.1.1.1.5." . $index;
        $usage = $entry['cpmCPUTotal5min'];
      }

      if ($entPhysicalIndex) {
        $descr_oid = "entPhysicalName." . $entPhysicalIndex;
        $descr = snmp_get($device, $descr_oid, "-Oqv", "ENTITY-MIB");
      }
      if (!$descr) { $descr = "Processor $index"; }

      $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("cpmCPU-" . $index . ".rrd");
      $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("processor-cpm-" . $index . ".rrd");

      if (is_file($old_rrd))
      {
        rename($old_rrd,$new_rrd);
        if ($debug) { echo("$old_rrd $new_rrd"); }
        echo("Moved RRD ");
      }

      if (!strstr($descr, "No") && !strstr($usage, "No") && $descr != "")
      {
        //discover_processor($valid['processor'], $device, $usage_oid, $index, "cpm", $descr, "1", $entry['juniSystemModuleCpuUtilPct'], $entPhysicalIndex, NULL);
        discover_processor($valid['processor'], $device, $usage_oid, $index, "cpm", $descr, "1", $usage, $entPhysicalIndex, NULL);
      }
    }
  }

  if (!is_array($valid['processor']['cpm']))
  {
    $avgBusy5 = snmp_get($device, ".1.3.6.1.4.1.9.2.1.58.0", "-Oqv");
    
    if (is_numeric($avgBusy5))
    {
      discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.9.2.1.58.0", "0", "ios", "Processor", "1", $avgBusy5, NULL, NULL);
    }
  }
}
// End Cisco Processors

unset ($processors_array);

?>
