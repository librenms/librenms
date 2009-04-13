#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

$start = utime();

### Observer Device Discovery

echo("Observer v".$config['version']." Discovery\n\n");

if($argv[1] == "--device" && $argv[2]) {
  $where = "AND `device_id` = '".$argv[2]."'";
} elseif ($argv[1] == "--os") {
  $where = "AND `os` = '".$argv[2]."'";
} elseif ($argv[1] == "--odd") {
  $where = "AND MOD(device_id,2) = 1";
} elseif ($argv[1] == "--even") {
  $where = "AND MOD(device_id,2) = 0";
} elseif ($argv[1] == "--all") {
  $where = "";
} else {
  echo("--device <device id>    Poll single device\n");
  echo("--os <os string>        Poll all devices of a given OS\n");
  echo("--all                   Poll all devices\n\n");
  echo("No polling type specified!\n");
  exit;
}

if ($argv[2] == "--type" && $argv[3]) {
  $type = $argv[3];
} elseif ($argv[3] == "--type" && $argv[4]) {
  $type = $argv[4];
} else {
  echo("Require valid discovery type.\n");
  exit;
}

$devices_polled = 0;

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' $where ORDER BY device_id ASC");
while ($device = mysql_fetch_array($device_query)) {

  echo("\n" . $device['hostname'] ."\n");

  include("includes/discovery/".$type.".php");
#  include("includes/discovery/ipv6-addresses.php");
#  include("includes/discovery/cisco-pw.php");

#  include("includes/discovery/host-physical.php");

# include("includes/discovery/bgp-peers.php");

  echo("\n"); $devices_polled++;
}

$end = utime(); $run = $end - $start;
$proctime = substr($run, 0, 5);

echo("$devices_polled devices polled in $proctime secs\n");


?>
