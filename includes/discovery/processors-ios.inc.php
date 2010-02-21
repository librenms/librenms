<?php

  if($device['os'] == "ios" || $device['os_group'] == "ios")
  {
    echo("CISCO-PROCESS-MIB : ");
    $processors_array = snmpwalk_cache_oid("cpmCPU", $device, $processors_array, "CISCO-PROCESS-MIB");
    if($debug) { print_r($processors_array); }
    foreach($processors_array[$device['device_id']] as $index => $entry) 
    {
      if ($entry['cpmCPUTotal5minRev'] || $entry['cpmCPUTotal5min']) 
      {
        $entPhysicalIndex = $entry['cpmCPUTotalPhysicalIndex'];

        if($entry['cpmCPUTotal5minRev']) {
          $usage_oid = ".1.3.6.1.4.1.9.9.109.1.1.1.1.8." . $index;
          $usage = $entry['cpmCPUTotal5minRev'];
        } elseif($entry['cpmCPUTotal5min']) {
          $usage_oid = ".1.3.6.1.4.1.9.9.109.1.1.1.1.5." . $index;
          $usage = $entry['cpmCPUTotal5min'];
        }

        $descr_oid = "entPhysicalName." . $entPhysicalIndex;
        $descr = snmp_get($device, $descr_oid, "-Oqv", "ENTITY-MIB");

        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("cpmCPU-" . $index . ".rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("processor-cpm-" . $index . ".rrd");

        if($debug) { echo("$old_rrd $new_rrd"); }
        if (is_file($old_rrd)) {
          shell_exec("mv -f $old_rrd $new_rrd");
          echo("Moved RRD ");
        }

        if(!strstr($descr, "No") && !strstr($usage, "No") && $descr != "" ) 
        {
          discover_processor($valid_processor, $device, $usage_oid, $index, "cpm", $descr, "1", $entry['juniSystemModuleCpuUtilPct'], $entPhysicalIndex, NULL);
        }
      }
    }
    if(!is_array($valid_processor['cpm'])) {
      $avgBusy5 = snmp_get($device, ".1.3.6.1.4.1.9.2.1.58.0", "-Oqv");
      if(is_numeric($avgBusy5)) {
        discover_processor($valid_processor, $device, ".1.3.6.1.4.1.9.2.1.58.0", "0", "ios", "Processor", "1", $avgBusy5, NULL, NULL);
      }
    }
  } 
  ## End Cisco Processors

  unset ($processors_array);

?>
