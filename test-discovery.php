#!/usr/bin/php
<?php


include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");
include("includes/discovery/functions.inc.php");

$start = utime();

### Observium Device Discovery

echo("Observium v".$config['version']." Discovery\n\n");

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
  $discovery_type = $argv[3];
} elseif ($argv[3] == "--type" && $argv[4]) {
  $discovery_type = $argv[4];
} else {
  echo("Require valid discovery type.\n");
  exit;
}

$devices_polled = 0;

  echo("includes/discovery/".$discovery_type.".php \n");

#$debug = 1;


$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' $where ORDER BY device_id ASC");
while ($device = mysql_fetch_array($device_query)) {

  echo($device['hostname'] . "(".$device['sysName']."|".$device['device_id'].")\n");
  if($config['os'][$device['os']]['group']) {$device['os_group'] = $config['os'][$device['os']]['group']; echo "(".$device['os_group'].")";}

  include("includes/discovery/".$discovery_type);

  echo("\n"); $devices_polled++;
}

$end = utime(); $run = $end - $start;
$proctime = substr($run, 0, 5);

echo("$devices_polled devices polled in $proctime secs\n $mysql SQL");


?>
