#!/usr/bin/php
<?
#
# Test Reachability
#

include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE `device_id` LIKE '%" . $argv[1] . "' ORDER BY `device_id` DESC");
while ($device = mysql_fetch_array($device_query)) {

   $id = $device['device_id'];
   $hostname = $device['hostname'];
   $old_status = $device['status'];
   $community = $device['community'];
   $snmpver = $device['snmpver'];

   echo("$hostname\n");

   $status = `$fping $hostname | cut -d " " -f 3`;
   $status = trim($status);

   if(strstr($status, "alive")) {
     $pos = `snmpget -$snmpver -c $community -t 1 $hostname sysDescr.0`;
#     echo("pos - $pos/n");
     if($pos == '') { 
       $status='0';
       $posb = `snmpget -$snmpver -c $community -t 1 $hostname 1.3.6.1.2.1.7526.2.4`;
       if($posb == '') { } else { 
         $status='1'; 
#         echo("posb - $posb/n");
       }
     } else { 
       $status='1'; 
     }
   } else {
     $status='0';
   }

   if($status != $device['status']) {

     if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }

     mysql_query("UPDATE `devices` SET `status`= '$status' WHERE `device_id` = '" . $device['device_id'] . "'");
     if ($status == '1') { 
       $stat = "Up"; 
       mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('0', '" . $device['device_id'] . "', 'Device is up\n')");
       mail($email, "DeviceUp: " . $device['hostname'], "Device Up: " . $device['hostname'] . " at " . date('l dS F Y h:i:s A'), $config['email_headers']);
     } else {
       $stat = "Down";
       mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('9', '" . $device['device_id'] . "', 'Device is down\n')");
       mail($email, "Device Down: " . $device['hostname'], "Device Down: " . $device['hostname'] . " at " . date('l dS F Y h:i:s A'), $config['email_headers']);
     }
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Device status changed to $stat')");
     echo("Status Changed!\n");
   }
}
?>
