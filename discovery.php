#!/usr/bin/php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");
include("includes/discovery/functions.inc.php");

$start = utime();
$runtime_stats = array();

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
  if (is_numeric($options['h']))
  {
    $where = "AND `device_id` = '".$options['h']."'";  $doing = "Host ".$options['h'];
  }
  else
  {
    $where = "AND `hostname` LIKE '".str_replace('*','%',mres($options['h']))."'";  $doing = "Host ".$options['h'];
  }
} elseif ($options['i'] && isset($options['n'])) {
  $where = "AND MOD(device_id,".$options['i'].") = '" . $options['n'] . "'";  $doing = "Proc ".$options['n'] ."/".$options['i'];
}

if(!$where) {
  echo("-h <device id> | <device hostname>  Poll single device\n");
  echo("-h odd                              Poll odd numbered devices  (same as -i 2 -n 0)\n");
  echo("-h even                             Poll even numbered devices (same as -i 2 -n 1)\n");
  echo("-h all                              Poll all devices\n\n");
  echo("-i <instances> -n <number>          Poll as instance <number> of <instances>\n");
  echo("                                    Instances start at 0. 0-3 for -n 4\n\n");
  echo("-d                                  Enable some debugging output\n");
  echo("\n");
  echo("No polling type specified!\n");
  exit;
 }

if (file_exists('.svn'))
{
  list(,$dbu_rev) = preg_split('/: /',@shell_exec('svn info database-update.sql|grep ^Revision'));

  $device_query = mysql_query("SELECT revision FROM `dbSchema`");
  if ($rev = @mysql_fetch_array($device_query)) 
  {
    $db_rev = $rev['revision'];
  } 
  else 
  { 
    $db_rev = 0;
  }

  if ($db_rev+0 < "1223") {
    include("fix-events.php"); ## Fix events table (needs to copy some data around, so needs script)
  }

  if ($dbu_rev+0 > $db_rev)
  {
    if($db_rev+0 < "1000") {
      echo("SVN revision changed.\nRunning pre-revision 1000 SQL update script...\n");
      shell_exec("scripts/update-sql.php database-update-pre1000.sql");
    }
    echo("SVN revision changed.\nRunning development SQL update script from r$db_rev to r" . trim($dbu_rev) . "...\n");
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

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = 1 AND disabled = 0 $where ORDER BY device_id DESC");
while ($device = mysql_fetch_array($device_query))
{
  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");
  if($device['os'] != strtolower($device['os'])) {
    mysql_query("UPDATE `devices` SET `os` = '".strtolower($device['os'])."' WHERE device_id = '".$device['device_id']."'");
    $device['os'] = strtolower($device['os']); echo("OS lowercased.");
  }
  if($config['os'][$device['os']]['group']) {$device['os_group'] = $config['os'][$device['os']]['group']; echo "(".$device['os_group'].")";}

  echo("\n");

  #include("includes/discovery/os.inc.php");

  include("includes/discovery/ports.inc.php");
  include("includes/discovery/entity-physical.inc.php");
  include("includes/discovery/processors.inc.php");
  include("includes/discovery/mempools.inc.php");
  include("includes/discovery/ipv4-addresses.inc.php");
  include("includes/discovery/ipv6-addresses.inc.php");
  include("includes/discovery/temperatures.inc.php");
  include("includes/discovery/humidity.inc.php");
  include("includes/discovery/voltages.inc.php");
  include("includes/discovery/frequencies.inc.php");
  include("includes/discovery/current.inc.php");
  include("includes/discovery/fanspeeds.inc.php");
  include("includes/discovery/storage.inc.php");
  include("includes/discovery/hr-device.inc.php");
  include("includes/discovery/discovery-protocols.inc.php");
  include("includes/discovery/arp-table.inc.php");
  include("includes/discovery/junose-atm-vp.inc.php");
  include("includes/discovery/bgp-peers.inc.php");
  include("includes/discovery/q-bridge-mib.inc.php");
  include("includes/discovery/cisco-vlans.inc.php");
  include("includes/discovery/cisco-mac-accounting.inc.php");
  include("includes/discovery/cisco-pw.inc.php");
  include("includes/discovery/cisco-vrf.inc.php");
  include("includes/discovery/toner.inc.php");
  include("includes/discovery/ups.inc.php");
  include("includes/discovery/ucd-diskio.inc.php");

  if ($device['type'] == "unknown")
  {
    switch ($device['os'])
    {
      case "procurve":
      case "powerconnect":
      case "ironware":
      case "allied":
      case "junos": # Could also be a Netscreen?
      case "ios":
      case "iosxe":
      case "catos":
      case "3com":
        $device['type'] = 'network'; 
        break;
      case "asa":
      case "pix":
      case "screenos":
        $device['type'] = 'firewall'; 
        break;
      case "dell-laser":
        $device['type'] = 'printer'; 
        break;
      case "linux":
        if (preg_match("/-server$/", $device['version'])) { $device['type'] = 'server'; }
        break;
      case "apc":
      case "mgeups":
      case "netmanplus":
        $device['type'] = 'power'; 
        break;
      case "akcp":
      case "minkelsrms":
      case "papouch-tme":
        $device['type'] = 'environment';
        break;
    }
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
