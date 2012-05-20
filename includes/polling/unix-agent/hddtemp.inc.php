<?php

global $agent_sensors;

include_once("includes/discovery/functions.inc.php");

$disks = explode('||',trim($agent_data['hddtemp'],'|'));

if (count($disks))
{
  echo "hddtemp: ";
  foreach ($disks as $disk)
  {
    list($blockdevice,$descr,$temperature,$unit) = explode('|',$disk,4);
    $diskcount++;
    discover_sensor($valid['sensor'], 'temperature', $device, '', $diskcount, 'hddtemp', "$blockdevice: $descr", '1', '1', NULL, NULL, NULL, NULL, $temperature, 'agent');

    $agent_sensors['temperature']['hddtemp'][$diskcount] = array('description' => "$blockdevice: $descr", 'current' => $temperature, 'index' => $diskcount);
  }
  echo "\n";
}

?>