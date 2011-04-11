#!/usr/bin/env php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE `device_id` LIKE '%" . $argv[1] . "' AND disabled = '0' ORDER BY `device_id` DESC");

while ($device = mysql_fetch_assoc($device_query))
{
  $port = $device['port'];

  echo($device['hostname']. " ");

  if (isPingable($device['hostname']))
  {
    $pos = snmp_get($device, "sysDescr.0", "-Oqv", "SNMPv2-MIB");
    echo($device['protocol'].":".$device['hostname'].":".$device['port']." - ".$device['community']." ".$device['snmpver'].": ");
    if ($pos == '')
    {
      $status='0';
    } else {
      $status='1';
    }
  } else {
    $status='0';
  }

  if ($status == '1')
  {
    echo("Up\n");
  } else {
    echo("Down\n");
  }

  if ($status != $device['status'])
  {
    mysql_query("UPDATE `devices` SET `status`= '$status' WHERE `device_id` = '" . $device['device_id'] . "'");

    if ($status == '1')
    {
      $stat = "Up";
      mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('0', '" . $device['device_id'] . "', 'Device is up\n')");
      if ($config['alerts']['email']['enable'])
      {
        notify($device, "Device Up: " . $device['hostname'], "Device Up: " . $device['hostname'] . " at " . date($config['timestamp_format']));
      }
    } else {
      $stat = "Down";
      mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('9', '" . $device['device_id'] . "', 'Device is down\n')");
      if ($config['alerts']['email']['enable'])
      {
        notify($device, "Device Down: " . $device['hostname'], "Device Down: " . $device['hostname'] . " at " . date($config['timestamp_format']));
      }
    }

    log_event("Device status changed to $stat", $device, strtolower($stat));
    echo("Status Changed!\n");
  }
}

?>
