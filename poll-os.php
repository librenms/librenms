#!/usr/bin/php
<?
#
# Statistics Polling Script
#

include("config.php");
include("includes/functions.php");

#
# Poll Device Statistics
#

$device_query = mysql_query("SELECT id,hostname,os,community,snmpver FROM `devices` WHERE `id` LIKE '%" . $argv[1] . "' AND status = '1' ORDER BY id DESC");
while ($device = mysql_fetch_array($device_query)) {

   $id = $device['id'];
   $hostname = $device['hostname'];
   $old_os = $device['os'];
   $community = $device['community'];
   $host = trim(strtolower($hostname));
   $host_os = getHostOS($host, $community, $device[snmpver]);
   if($old_os != $host_os) {
      $sql = mysql_query("UPDATE `devices` SET `os` = '$host_os' WHERE `id` = '$id'");
      echo("Updated host : $host ($host_os)\n");
   } else echo("Not Updated host : $host ($host_os)\n");
}
?>
