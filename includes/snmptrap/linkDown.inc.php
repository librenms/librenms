<?php

$interface = mysql_fetch_assoc(mysql_query("SELECT * FROM `ports` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '".$entry[2]."'"));

if (!$interface) { exit; }

$ifOperStatus = "down";
#$ifAdminStatus = "down";

log_event("SNMP Trap: linkDown " . $interface['ifDescr'], $device, "interface", $interface['interface_id']);

#if ($ifAdminStatus != $interface['ifAdminStatus'])
#{
#  log_event("Interface Disabled : " . $interface['ifDescr'] . " (TRAP)", $device, "interface", $interface['interface_id']);
#}
if ($ifOperStatus != $interface['ifOperStatus'])
{
  log_event("Interface went Down : " . $interface['ifDescr'] . " (TRAP)", $device, "interface", $interface['interface_id']);
  mysql_query("UPDATE `ports` SET ifOperStatus = 'down' WHERE `interface_id` = '".$interface['interface_id']."'");
}

?>