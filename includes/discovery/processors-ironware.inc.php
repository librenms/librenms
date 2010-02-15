<?php

  if($device['os'] == "ironware" || $device['os_group'] == "ironware")
  {
    echo("IronWare : ");
    $processors_array = snmpwalk_cache_triple_oid($device, "snAgentCpuUtilEntry", $processors_array, "FOUNDRY-SN-AGENT-MIB");
    if($debug) { print_r($processors_array); }
    foreach($processors_array[$device['device_id']] as $index => $entry) 
    {
      if (($entry['snAgentCpuUtilValue'] || $entry['snAgentCpuUtil100thPercent']) && $entry['snAgentCpuUtilInterval'] == "300") 
      {
        #$entPhysicalIndex = $entry['cpmCPUTotalPhysicalIndex'];

        if($entry['snAgentCpuUtil100thPercent']) {
          $usage_oid = ".1.3.6.1.4.1.1991.1.1.2.11.1.1.6." . $index;
          $usage = $entry['snAgentCpuUtil100thPercent'];
	  $precision = 100;
        } elseif($entry['snAgentCpuUtilValue']) {
          $usage_oid = ".1.3.6.1.4.1.1991.1.1.2.11.1.1.4." . $index;
          $usage = $entry['snAgentCpuUtilValue'];
          $precision = 1;
        }

        list($slot, $instance, $interval) = explode(".", $index);

        $descr_oid = "snAgentConfigModuleDescription." . $entry['snAgentCpuUtilSlotNum'];
        $descr = snmp_get($device, $descr_oid, "-Oqv", "FOUNDRY-SN-AGENT-MIB");
        $descr = str_replace("\"", "", $descr);
	list($descr) = explode(" ", $descr);

        $descr = "Slot " . $entry['snAgentCpuUtilSlotNum'] . " " . $descr;
        $descr = $descr . " [".$instance."]";

        if(!strstr($descr, "No") && !strstr($usage, "No") && $descr != "" ) 
        {
          if(mysql_result(mysql_query("SELECT count(processor_id) FROM `processors` WHERE `processor_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `processor_type` = 'ironware'"),0) == '0') {
            $query = "INSERT INTO processors (`entPhysicalIndex`, `device_id`, `processor_descr`, `processor_index`, `processor_oid`, `processor_usage`, `processor_type`, `processor_precision`)
                      values ('$entPhysicalIndex', '".$device['device_id']."', '$descr', '$index', '$usage_oid', '".$usage."', 'ironware', '$precision')";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
            echo("+");
          } else {
            echo(".");
            $query = "UPDATE `processors` SET `processor_descr` = '".$descr."', `processor_oid` = '".$usage_oid."', `processor_usage` = '".$usage."'
                      WHERE `device_id` = '".$device['device_id']."' AND `processor_index` = '".$index."' AND `processor_type` = 'ironware'";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
          }
          $valid_processor['ironware'][$index] = 1;
        }
      }
    }
  } 
  ## End Cisco Processors

  unset ($processors_array);

?>
