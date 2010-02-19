#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");
include("includes/functions-poller.inc.php");
include("includes/discovery/functions.inc.php");

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

if (file_exists('.svn'))
{
  list(,$dbu_rev) = split(': ',@shell_exec('svn info database-update.sql|grep ^Revision'));

  $device_query = mysql_query("SELECT revision FROM `dbSchema`");
  if ($rev = @mysql_fetch_array($device_query)) 
  {
    $db_rev = $rev['revision'];
  } 
  else 
  { 
    $db_rev = 0;
  }

  if ($dbu_rev+0 > $db_rev)
  {
    echo("Applying database updates to from r$db_rev to r" . trim($dbu_rev) . "...\n");
    shell_exec("scripts/update-sql.php database-update.sql");
    if ($db_rev == 0)
    {
      mysql_query("INSERT INTO dbSchema VALUES ($dbu_rev)");
    }
    else
    {
      mysql_query("UPDATE dbSchema set revision=$dbu_rev");
    }
  }
}

if(isset($options['d'])) { echo("DEBUG!\n"); $debug = 1; } else { $debug = 0; }


$devices_discovered = 0;

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' $where ORDER BY device_id DESC");
while ($device = mysql_fetch_array($device_query))
{
  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");
  if($device['os'] != strtolower($device['os'])) {
    mysql_query("UPDATE `devices` SET `os` = '".strtolower($device['os'])."' WHERE device_id = '".$device['device_id']."'");
    $device['os'] = strtolower($device['os']); echo("OS lowercased.");
  }
  if($os_groups[$device['os']]) {$device['os_group'] = $os_groups[$device['os']]; echo "(".$device['os_group'].")";}

  echo("\n");

  ## Discover OS Changes
  #include("includes/discovery/os.inc.php");

  ## Discover Interfaces 
  include("includes/discovery/interfaces.php");

  ## Discovery ENTITY-MIB 
#  include("includes/discovery/entity-physical.inc.php");

  include("includes/discovery/processors.inc.php");

  ## Discover IPv4 Addresses
  include("includes/discovery/ipv4-addresses.php");

  ## Discovery IPv6 Addresses
  include("includes/discovery/ipv6-addresses.php");

  ## Discover Temperatures/Voltages/Fanspeeds
  include("includes/discovery/temperatures.php");
  include("includes/discovery/voltages.php");
  include("includes/discovery/fanspeeds.php");

  ## Discover Storage
  include("includes/discovery/storage.php");

  ## hr-device.inc.php
  include("includes/discovery/hr-device.inc.php");

  ## CDP & LLDP
  include("includes/discovery/cdp-lldp.inc.php");

  ## ARP Table
  include("includes/discovery/arp-table.php");

  if($device['os'] == "screenos") { 
    if ($device['type'] == "unknown") { $device['type'] = 'firewall'; }
  }

  if($device['os'] == "junos") { 
    include("includes/discovery/bgp-peers.php"); 
    if ($device['type'] == "unknown") { $device['type'] = 'network'; } # FIXME: could also be a Netscreen...
  }
  
  if($device['os'] == "junose") { 
    include("includes/discovery/bgp-peers.php"); 
    include("includes/discovery/processors-junose.inc.php");
  }
  
  if ($device['os_group'] == "unix")
  {
    # Also discover quagga peers
    include("includes/discovery/bgp-peers.php");
  }

  if($device['os'] == "ios" || $device['os'] == "iosxe" || $device['os'] == "catos" || $device['os'] == "asa" || $device['os'] == "pix") {
    include("includes/discovery/cisco-vlans.php");
    include("includes/discovery/bgp-peers.php");
    include("includes/discovery/cisco-mac-accounting.php");
    include("includes/discovery/cisco-pw.php");
    include("includes/discovery/cisco-vrf.php");
    #include("includes/discovery/cisco-processors.php");
    include("includes/discovery/cemp-mib.php");
    include("includes/discovery/cmp-mib.php");

    if ($device['type'] == "unknown") { $device['type'] = 'network'; };
  }

  if ($device['os'] == "procurve" || $device['os'] == "powerconnect")
  {
    include("includes/discovery/q-bridge-mib.php");
    if ($device['type'] == "unknown") { $device['type'] = 'network'; };
  }

  if ($device['os'] == "asa" || $device['os'] == "pix")
  {
    if ($device['type'] == "unknown") { $device['type'] = 'firewall'; }
  }

  if ($device['os'] == "dell-laser")
  {
    if ($device['type'] == "unknown") { $device['type'] = 'printer'; }
  }

  $update_query  = "UPDATE `devices` SET ";
  $update_query .= " `last_discovered` = NOW(), `type` = '" . $device['type'] . "'";
  $update_query .= " WHERE `device_id` = '" . $device['device_id'] . "'";
  $update_result = mysql_query($update_query);

  echo("\n"); $devices_discovered++;
}

$end = utime(); $run = $end - $start;
$proctime = substr($run, 0, 5);

echo("$devices_discovered devices discovered in $proctime secs\n");


?>
