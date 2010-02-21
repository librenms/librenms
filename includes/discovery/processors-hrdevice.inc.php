<?php

  if($device['os_group'] == "unix" || $device['os'] == "windows")
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
          rename($old_rrd,$new_rrd);
          echo("Moved RRD ");
        }
        if(!strstr($descr, "No") && !strstr($usage, "No") && $descr != "" && $descr != "An electronic chip that makes the computer work.") 
        {
          discover_processor($valid_processor, $device, $usage_oid, $index, "hr", $descr, "1", $usage, NULL, $hrDeviceIndex);
        }
      }
    }
  } 
  ## End hrDevice Processors
  unset ($processors_array);


?>
