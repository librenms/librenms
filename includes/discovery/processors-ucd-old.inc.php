<?php

$count = mysql_result(mysql_query("SELECT COUNT(*) FROM processors WHERE device_id = '".$device['device_id']."' AND processor_type != 'ucd-old'"),0);

if ($device['os_group'] == "unix" && $count == "0")
{
  echo("UCD Old: ");

  $system = snmp_get($device, "ssCpuSystem.0", "-OvQ", "UCD-SNMP-MIB");
  $user   = snmp_get($device, "ssCpuUser.0"  , "-OvQ", "UCD-SNMP-MIB");
  $idle   = snmp_get($device, "ssCpuIdle.0"  , "-OvQ", "UCD-SNMP-MIB");

  $percent = $system + $user + $idle;

  if (is_numeric($percent))
  {
    discover_processor($valid_processor, $device, 0, 0, "ucd-old", "CPU", "1", $system+$user, NULL, NULL);
  }
}

?>