#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT device_id,hostname,os,community,snmpver FROM `devices` WHERE `device_id` LIKE '%" . $argv[1] . "' AND status = '1' ORDER BY device_id DESC");
while ($device = mysql_fetch_array($device_query)) {

   $os = getHostOS($device['hostname'], $device['community'], $device[snmpver]);

   if($os != $device['os']) {
      $sql = mysql_query("UPDATE `devices` SET `os` = '$os' WHERE `device_id` = '".$device['device_id']."'");
      echo("Updated host : ".$device['hostname']." ($os)\n");
   }
}
?>
