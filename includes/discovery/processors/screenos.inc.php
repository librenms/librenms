<?php

if ($device['os'] == "screenos" && dbFetchCell("SELECT COUNT(*) FROM `processors` WHERE `device_id` = ? AND `processor_type` != 'screenos'",array($device['device_id'])) == "0")
{
  # .1.3.6.1.4.1.3224.16.1.3.0 Cpu Last 5 Minutes
  # discover_processor(&$valid, $device, $oid, $index, $type, $descr, $precision = "1", $current = NULL, $entPhysicalIndex = NULL, $hrDeviceIndex = NULL)

  echo("ScreenOS ");

  $percent = snmp_get($device, ".1.3.6.1.4.1.3224.16.1.3.0", "-OvQ");

  if (is_numeric($percent))
  {
    discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.3224.16.1.3.0", "1", "screenos", "Processor", "1", $percent, NULL, NULL);
  }
}

?>
