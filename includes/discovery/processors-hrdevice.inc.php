<?php

  if($device['os_group'] == "unix")
  {
    echo("hrDevice ");
    $hrDevice_oids = array('hrDevice','hrProcessorLoad');  
    if($debug) { print_r($processors_array); }
    foreach ($hrDevice_oids as $oid) { $hrDevice_array = snmp_cache_oid($oid, $device, $hrDevice_array, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES"); }
    foreach($hrDevice_array[$device['device_id']] as $index => $entry)
    {
      if ($entry['hrDeviceType'] == "hrDeviceProcessor") 
      {
        $hrDeviceIndex = $entry['hrDeviceIndex'];

        $usage_oid = ".1.3.6.1.2.1.25.3.3.1.2." . $index;
        $usage = $entry['hrProcessorLoad'];

        $descr = $entry['hrDeviceDescr'];

        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("hrProcessor-" . $index . ".rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("processor-hr-" . $index . ".rrd");

        if($debug) { echo("$old_rrd $new_rrd"); }
        if (is_file($old_rrd)) {
          shell_exec("mv -f $old_rrd $new_rrd");
          echo("Moved RRD ");
        }

        if(!strstr($descr, "No") && !strstr($usage, "No") && $descr != "" ) 
        {
          if(mysql_result(mysql_query("SELECT count(processor_id) FROM `processors` WHERE `processor_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `processor_type` = 'hr'"),0) == '0') {
            $query = "INSERT INTO processors (`hrDeviceIndex`, `device_id`, `processor_descr`, `processor_index`, `processor_oid`, `processor_usage`, `processor_type`)
                      values ('$hrDeviceIndex', '".$device['device_id']."', '$descr', '$index', '$usage_oid', '".$usage."', 'hr')";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
            echo("+");
          } else {
            echo(".");
            $query = "UPDATE `processors` SET `processor_descr` = '".$descr."', `processor_oid` = '".$usage_oid."', `processor_usage` = '".$usage."'
                      WHERE `device_id` = '".$device['device_id']."' AND `processor_index` = '".$index."' AND `processor_type` = 'hr'";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
          }
          $valid_processor['hr'][$index] = 1;
        }
      }
    }
  } 
  ## End hrDevice Processors
  unset ($processors_array);


?>
