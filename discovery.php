#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");
include("includes/functions-poller.inc.php");

$start = utime();

### Observer Device Discovery

echo("Observer v".$config['version']." Discovery\n\n");

$options = getopt("h:t:i:n:d::a::");

if ($options['h'] == "odd") {
  $where = "AND MOD(device_id,2) = 1";  $doing = $options['h'];
} elseif ($options['h'] == "even") {
  $where = "AND MOD(device_id,2) = 0";  $doing = $options['h'];
} elseif ($options['h'] == "all") {
  $where = " ";  $doing = "all";
} elseif($options['h']) {
  $where = "AND `device_id` = '".$options['h']."'";  $doing = "Host ".$options['h'];
} elseif ($options['i'] && isset($options['n'])) {
  $where = "AND MOD(device_id,".$options['i'].") = '" . $options['n'] . "'";  $doing = "Proc ".$options['n'] ."/".$options['i'];
}

if(!$where) {
  echo("-h <device id>                Poll single device\n");
  echo("-h odd                        Poll odd numbered devices  (same as -i 2 -n 0)\n");
  echo("-h even                       Poll even numbered devices (same as -i 2 -n 1)\n");
  echo("-h all                        Poll all devices\n\n");
  echo("-i <instances> -n <number>    Poll as instance <number> of <instances>\n");
  echo("                              Instances start at 0. 0-3 for -n 4\n\n");
  echo("-d                            Enable some debugging output\n");
  echo("\n");
  echo("No polling type specified!\n");
  exit;
 }

echo("Applying database updates...\n");
shell_exec("scripts/update-sql.php database-update.sql");

if(isset($options['d'])) { echo("DEBUG!\n"); $debug = 1; }


$devices_discovered = 0;

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' $where ORDER BY device_id DESC");
while ($device = mysql_fetch_array($device_query)) {

  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");
  if($os_groups[$device[os]]) {$device['os_group'] = $os_groups[$device[os]]; echo "(".$device['os_group'].")";}
  echo("\n");

  ## Discover OS Changes
#  include("includes/discovery/os.inc.php");

  ## Discover Interfaces 
  include("includes/discovery/interfaces.php");

  ## Discovery ENTITY-MIB 
  include("includes/discovery/entity-physical.inc.php");

  ## Discover IPv4 Addresses
  include("includes/discovery/ipv4-addresses.php");

  ## Discovery IPv6 Addresses
  include("includes/discovery/ipv6-addresses.php");

  ## Discover Temperatures
  include("includes/discovery/temperatures.php");

  ## Discover Storage
  include("includes/discovery/storage.php");

  ## hr-device.inc.php
  include("includes/discovery/hr-device.inc.php");

  if($device['os'] == "Netscreen") { }

  if($device['os'] == "JunOS") { include("includes/discovery/bgp-peers.php"); }

  if($device['os'] == "powerconnect" || $device['os'] == "ios" || $device['os'] == "iosxe" || $device['os'] == "catos" || $device['os'] == "asa" || $device['os'] == "pix") {
    include("includes/discovery/cisco-vlans.php");
    include("includes/discovery/bgp-peers.php");
    include("includes/discovery/cisco-mac-accounting.php");
    include("includes/discovery/cisco-pw.php");
    include("includes/discovery/cisco-vrf.php");
    include("includes/discovery/cisco-processors.php");
    include("includes/discovery/cemp-mib.php");
    include("includes/discovery/cmp-mib.php");
    include("includes/discovery/cisco-cdp.inc.php");
  }

  $update_query  = "UPDATE `devices` SET ";
  $update .= " `last_discovered` = NOW()";
  $update_query .= " WHERE `device_id` = '" . $device['device_id'] . "'";
  $update_result = mysql_query($update_query);

  echo("\n"); $devices_discovered++;
}

$end = utime(); $run = $end - $start;
$proctime = substr($run, 0, 5);

echo("$devices_discovered devices discovered in $proctime secs\n");


?>
