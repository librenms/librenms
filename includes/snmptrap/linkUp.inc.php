<?php

$interface = mysql_fetch_assoc(mysql_query("SELECT * FROM `ports` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '".$entry[2]."'"));

if (!$interface) { exit; }

$ifOperStatus = "up";
$ifAdminStatus = "up";

log_event("SNMP Trap: linkUp $ifAdminStatus/$ifOperStatus " . $interface['ifDescr'], $device, "interface", $interface['port_id']);

if ($ifAdminStatus != $interface['ifAdminStatus'])
{
  log_event("Interface Enabled : " . $interface['ifDescr'] . " (TRAP)", $device, "interface", $interface['port_id']);
  mysql_query("UPDATE `ports` SET ifAdminStatus = 'up' WHERE `port_id` = '".$interface['port_id']."'");
}

if ($ifOperStatus != $interface['ifOperStatus'])
{
  log_event("Interface went Up : " . $interface['ifDescr'] . " (TRAP)", $device, "interface", $interface['port_id']);
  mysql_query("UPDATE `ports` SET ifOperStatus = 'up' WHERE `port_id` = '".$interface['port_id']."'");

}

?>