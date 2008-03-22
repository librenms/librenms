#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

$start = utime();

### Observer Device Discovery

echo("Observer v".$config['version']." Discovery\n\n");

$devices_polled = 0;

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1'");
while ($device = mysql_fetch_array($device_query)) {

  echo($device['hostname'] ."\n");

  ## Discover Interfaces 
  include("includes/discovery/interfaces.php");

  ## Discover IP Addresses
  include("includes/discovery/ipaddresses.php");

  ## Discover Temperatures
  include("includes/discovery/temperatures.php");

  if($device['os'] == "Linux") {
    include("includes/discovery/storage.php");
  }

  if($device['os'] == "IOS") {
    include("includes/discovery/cisco-vlans.php");
    include("includes/discovery/cisco-physical.php");
    include("includes/discovery/bgp-peers.php");
  }


  echo("\n"); $devices_polled++;
}

$end = utime(); $run = $end - $start;
$proctime = substr($run, 0, 5);

echo("$devices_polled devices polled in $proctime secs\n");


?>
