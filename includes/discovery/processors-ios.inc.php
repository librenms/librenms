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
          if(mysql_result(mysql_query("SELECT count(processor_id) FROM `processors` WHERE `processor_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `processor_type` = 'cpm'"),0) == '0') {
            $query = "INSERT INTO processors (`entPhysicalIndex`, `device_id`, `processor_descr`, `processor_index`, `processor_oid`, `processor_usage`, `processor_type`)
                      values ('$entPhysicalIndex', '".$device['device_id']."', '$descr', '$index', '$usage_oid', '".$entry['juniSystemModuleCpuUtilPct']."', 'cpm')";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
            echo("+");
          } else {
            echo(".");
            $query = "UPDATE `processors` SET `processor_descr` = '".$descr."', `processor_oid` = '".$usage_oid."', `processor_usage` = '".$usage."'
                      WHERE `device_id` = '".$device['device_id']."' AND `processor_index` = '".$index."' AND `processor_type` = 'cpm'";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
          }
          $valid_processor['cpm'][$index] = 1;
        }
      }
    }
  } 
  ## End Cisco Processors

  unset ($processors_array);

?>
