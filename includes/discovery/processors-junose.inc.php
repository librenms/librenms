<?php

  ## JUNOSe Processors
  if($device['os'] == "junose")
  {
    echo("JUNOSe : ");
    $processors_array = snmpwalk_cache_double_oid($device, "juniSystemModule", $processors_array, "Juniper-System-MIB" , "+".$config['install_dir']."/mibs/junose");
    if($debug) { print_r($processors_array); }

    foreach($processors_array[$device['device_id']] as $index => $entry) {
      if ($entry['juniSystemModuleCpuUtilPct'] && $entry['juniSystemModuleCpuUtilPct'] != "-1") {   
        $entPhysicalIndex = $entry['juniSystemModulePhysicalIndex'];
        $usage_oid = ".1.3.6.1.4.1.4874.2.2.2.1.3.5.1.3." . $index;
        $descr_oid = ".1.3.6.1.4.1.4874.2.2.2.1.3.5.1.6." . $index;
        $descr = $entry['juniSystemModuleDescr'];
        $usage = $entry['juniSystemModuleCpuFiveMinAvgPct'];
        if(!strstr($descr, "No") && !strstr($usage, "No") && $descr != "" ) {
          if(mysql_result(mysql_query("SELECT count(processor_id) FROM `processors` WHERE `processor_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `processor_type` = 'junose'"),0) == '0') {
            $query = "INSERT INTO processors (`entPhysicalIndex`, `device_id`, `processor_descr`, `processor_index`, `processor_oid`, `processor_usage`, `processor_type`) 
                      values ('$entPhysicalIndex', '".$device['device_id']."', '$descr', '$index', '$usage_oid', '".$entry['juniSystemModuleCpuUtilPct']."', 'junose')";
            mysql_query($query);
	    if($debug) { print $query . "\n"; }
            echo("+");
          }   else { 
            echo("."); 
            $query = "UPDATE `processors` SET `processor_descr` = '".$descr."', `processor_oid` = '".$usage_oid."', `processor_usage` = '".$usage."' 
                      WHERE `device_id` = '".$device['device_id']."' AND `processor_index` = '".$index."' AND `processor_type` = 'junose'";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
          }
          $valid_processor['junose'][$index] = 1;
        }
      }
    } 
  } ## End JUNOSe Processors

  unset ($processors_array);

?>
