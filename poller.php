#!/usr/bin/php
<?php

include("includes/defaults.inc.php");
include("config.php");

echo("Observer Poller v".$config['version']."\n\n");

include("includes/functions.php");

$poller_start = utime();

$options = getopt("h:t:i:n:d::a::"); 

if ($options['h'] == "odd") {
  $where = "AND MOD(device_id,2) = 1";
} elseif ($options['h'] == "even") {
  $where = "AND MOD(device_id,2) = 0";
} elseif($options['h']) {
  $where = "AND `device_id` = '".$options['h']."'"; 
} elseif ($options['i'] && isset($options['n'])) {
  $where = "AND MOD(device_id,".$options['i'].") = '" . $options['n'] . "'";  
} elseif ($options['h'] == "all") {
  $where = " ";
}

if(!$where) { 
  echo("-h <device id>                Poll single device\n");
  echo("-h odd                        Poll odd numbered devices  (same as -i 2 -n 0)\n");
  echo("-h even                       Poll even numbered devices (same as -i 2 -n 1)\n");
  echo("-a                            Poll all devices\n\n");
  echo("-i <instances> -n <number>    Poll as instance <number> of <instances>\n\n");
  echo("-d                            Enable some debugging output\n");
  echo("\n");
  echo("No polling type specified!\n");
  exit;
 }

if(isset($options['d'])) { echo("DEBUG!\n"); $debug = 1; }

$i = 0;
$device_query = mysql_query("SELECT * FROM `devices` WHERE `ignore` = '0' AND `disabled` = '0' AND `status` = '1' $where ORDER BY device_id DESC");
while ($device = mysql_fetch_array($device_query)) {

  echo("-> " . $device['hostname'] . "\n"); 

  $device_start = utime();  // Start counting device poll time

  $host_rrd = $config['rrd_dir'] . "/" . $device['hostname'];
  if(!is_dir($host_rrd)) { mkdir($host_rrd); echo("Created directory : $host_rrd\n"); }
  $i++;

  $device['pingable'] = isPingable($device['hostname']);
  if($device['pingable']) {
    $device['snmpable'] = isSNMPable($device['hostname'], $device['community'], $device['snmpver'], $device['port']);
  }

  if($device['pingable'] && $device['snmpable']) {  // Reachability Check
    if($options['t']) {
      include("includes/polling/".$options['t'].".inc.php");
    } else {
      include("includes/polling/ports.inc.php");
      include("includes/polling/ports-etherlike.inc.php");
      include("includes/polling/cisco-mac-accounting.inc.php");
    }
  } else { echo(" Unreachable"); } // End Reachability Check

  unset($array); // Clear $array SNMP cache

  $device_end = utime(); $device_run = $device_end - $device_start; $device_time = substr($device_run, 0, 5);

  echo("Polled in $device_time seconds\n");
  #shell_exec("echo 'Polled ".$device['hostname']." in $device_time seconds' >> /opt/observer/observer.log");
}


$poller_end = utime(); $poller_run = $poller_end - $poller_start; $poller_time = substr($poller_run, 0, 5);

$string = $argv[0] . " " . date("F j, Y, G:i") . " - $i devices polled in $poller_time secs";
echo("$string\n");

shell_exec("echo '".$string."' >> /opt/observer/observer.log");

?>
