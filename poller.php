#!/usr/bin/env php
<?php

/* Observium Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 */

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

$poller_start = utime();
echo("Observium Poller v".$config['version']."\n\n");

$options = getopt("h:t:i:n:d::a::");

if ($options['h'] == "odd")      { $options['n'] = "1"; $options['i'] = "2"; }
elseif ($options['h'] == "even") { $options['n'] = "0"; $options['i'] = "2"; }
elseif ($options['h'] == "all")  { $where = " "; $doing = "all"; }
elseif ($options['h'])
{
  if (is_numeric($options['h']))
  {
    $where = "AND `device_id` = '".$options['h']."'";
    $doing = $options['h'];
  }
  else
  {
    $where = "AND `hostname` LIKE '".str_replace('*','%',mres($options['h']))."'";
    $doing = $options['h'];
  }
}

if (isset($options['i']) && $options['i'] && isset($options['n']))
{
  $where = "AND MOD(device_id,".$options['i'].") = '" . $options['n'] . "'";
  $doing = $options['n'] ."/".$options['i'];
}

if (!$where)
{
  echo("-h <device id> | <device hostname wildcard>  Poll single device\n");
  echo("-h odd                                       Poll odd numbered devices  (same as -i 2 -n 0)\n");
  echo("-h even                                      Poll even numbered devices (same as -i 2 -n 1)\n");
  echo("-h all                                       Poll all devices\n\n");
  echo("-i <instances> -n <number>                   Poll as instance <number> of <instances>\n");
  echo("                                             Instances start at 0. 0-3 for -n 4\n\n");
  echo("-d                                           Enable some debugging output\n");
  echo("\n");
  echo("No polling type specified!\n");
  exit;
}

if (isset($options['d']))
{
  echo("DEBUG!\n");
  $debug = TRUE;
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', 1);
} else {
  $debug = FALSE;
#  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
#  ini_set('error_reporting', 0);
}

echo("Starting polling run:\n\n");
$polled_devices = 0;
$device_query = mysql_query("SELECT `device_id` FROM `devices` WHERE `ignore` = 0 AND `disabled` = 0 $where  ORDER BY `device_id` ASC");

while ($device = mysql_fetch_assoc($device_query))
{
  $device = mysql_fetch_assoc(mysql_query("SELECT * FROM `devices` WHERE `device_id` = '".$device['device_id']."'"));

  $status = 0; unset($array);
  $device_start = utime();  // Start counting device poll time

  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");
  if ($config['os'][$device['os']]['group'])
  {
    $device['os_group'] = $config['os'][$device['os']]['group'];
    echo("(".$device['os_group'].")");
  }
  echo("\n");

  unset($poll_update); unset($poll_update_query); unset($poll_separator); unset($version); unset($uptime); unset($features);
  unset($sysLocation); unset($hardware); unset($sysDescr); unset($sysContact); unset($sysName); unset($serial);

  $host_rrd = $config['rrd_dir'] . "/" . $device['hostname'];
  if (!is_dir($host_rrd)) { mkdir($host_rrd); echo("Created directory : $host_rrd\n"); }

  $device['pingable'] = isPingable($device['hostname']);
  if ($device['pingable'])
  {
    $device['snmpable'] = isSNMPable($device);
    if ($device['snmpable'])
    {
      $status = "1";
    } else {
      echo("SNMP Unreachable");
      $status = "0";
    }
  } else {
    echo("Unpingable");
    $status = "0";
  }

  if ($device['status'] != $status)
  {
    $poll_update .= $poll_separator . "`status` = '$status'";
    $poll_separator = ", ";
    mysql_query("UPDATE `devices` SET `status` = '".$status."' WHERE `device_id` = '".$device['device_id']."'");
    mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('0', '" . $device['device_id'] . "', 'Device is " .($status == '1' ? 'up' : 'down') . "')");
    log_event('Device status changed to ' . ($status == '1' ? 'Up' : 'Down'), $device, ($status == '1' ? 'up' : 'down'));
    notify($device, "Device ".($status == '1' ? 'Up' : 'Down').": " . $device['hostname'], "Device ".($status == '1' ? 'up' : 'down').": " . $device['hostname'] . " at " . date($config['timestamp_format']));
  }

  if ($status == "1")
  {
    $graphs = array();
    $oldgraphs = array();

    $snmpdata = snmp_get_multi($device, "sysUpTime.0 sysLocation.0 sysContact.0 sysName.0", "-OQUs", "SNMPv2-MIB");
    foreach (array_keys($snmpdata[0]) as $key) { $$key = $snmpdata[0][$key]; }

    $sysDescr = snmp_get($device, "sysDescr.0", "-Oqv", "SNMPv2-MIB");

    $sysName = strtolower($sysName);

    $hrSystemUptime = snmp_get($device, "hrSystemUptime.0", "-Oqv", "HOST-RESOURCES-MIB");
    $sysObjectID = snmp_get($device, "sysObjectID.0", "-Oqvn");

#    echo("UPTIMES: ".$hrSystemUptime."|".$sysUpTime."]");

    if ($hrSystemUptime != "" && !strpos($hrSystemUptime, "No") && ($device['os'] != "windows"))
    {
      echo("Using hrSystemUptime\n");
      $agent_uptime = $uptime; ## Move uptime into agent_uptime
      #HOST-RESOURCES-MIB::hrSystemUptime.0 = Timeticks: (63050465) 7 days, 7:08:24.65
      $hrSystemUptime = str_replace("(", "", $hrSystemUptime);
      $hrSystemUptime = str_replace(")", "", $hrSystemUptime);
      list($days,$hours, $mins, $secs) = explode(":", $hrSystemUptime);
      list($secs, $microsecs) = explode(".", $secs);
      $hours = $hours + ($days * 24);
      $mins = $mins + ($hours * 60);
      $secs = $secs + ($mins * 60);
      $uptime = $secs;
    } else {
      echo("Using Agent Uptime\n");
      #SNMPv2-MIB::sysUpTime.0 = Timeticks: (2542831) 7:03:48.31
      $sysUpTime = str_replace("(", "", $sysUpTime);
      $sysUpTime = str_replace(")", "", $sysUpTime);
      list($days, $hours, $mins, $secs) = explode(":", $sysUpTime);
      list($secs, $microsecs) = explode(".", $secs);
      $hours = $hours + ($days * 24);
      $mins = $mins + ($hours * 60);
      $secs = $secs + ($mins * 60);
      $uptime = $secs;
    }

    if (is_numeric($uptime))
    {
      if ($uptime < $device['uptime'])
      {
        notify($device,"Device rebooted: " . $device['hostname'],  "Device Rebooted : " . $device['hostname'] . " " . formatUptime($uptime) . " ago.");
        log_event('Device rebooted after '.formatUptime($device['uptime']), $device, 'reboot', $device['uptime']);
      }

      $uptimerrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/uptime.rrd";

      if (!is_file($uptimerrd))
      {
        rrdtool_create ($uptimerrd, "DS:uptime:GAUGE:600:0:U RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797");
      }
      rrdtool_update($uptimerrd, "N:$uptime");

      $graphs['uptime'] = TRUE;

      echo("Uptime: ".formatUptime($uptime)."\n");

      $poll_update .= $poll_separator . "`uptime` = '$uptime'";
      $poll_separator = ", ";
    }

    if (is_file($config['install_dir'] . "/includes/polling/os/".$device['os'].".inc.php"))
    {
      /// OS Specific
      include($config['install_dir'] . "/includes/polling/os/".$device['os'].".inc.php");
    }
    elseif ($device['os_group'] && is_file($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php"))
    {
      /// OS Group Specific
      include($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php");
    }
    else
    {
      echo("Generic :(\n");
    }

    echo("Hardware: ".$hardware." Version: ".$version." Features: ".$features."\n");

    $sysLocation = str_replace("\"","", $sysLocation);
    $sysContact  = str_replace("\"","", $sysContact);

    include("includes/polling/ipmi.inc.php");
    include("includes/polling/temperatures.inc.php");
    include("includes/polling/humidity.inc.php");
    include("includes/polling/fanspeeds.inc.php");
    include("includes/polling/voltages.inc.php");
    include("includes/polling/frequencies.inc.php");
    include("includes/polling/current.inc.php");
    include("includes/polling/processors.inc.php");
    include("includes/polling/mempools.inc.php");
    include("includes/polling/storage.inc.php");
    include("includes/polling/netstats.inc.php");
    include("includes/polling/hr-mib.inc.php");
    include("includes/polling/ucd-mib.inc.php");
    include("includes/polling/ipSystemStats.inc.php");
    include("includes/polling/ports.inc.php");
    include("includes/polling/cisco-mac-accounting.inc.php");
    include("includes/polling/bgp-peers.inc.php");
    include("includes/polling/junose-atm-vp.inc.php");
    include("includes/polling/toner.inc.php");
    include("includes/polling/ucd-diskio.inc.php");
    include("includes/polling/applications.inc.php");
    include("includes/polling/wifi.inc.php");

    #include("includes/polling/altiga-ssl.inc.php");
    include("includes/polling/cisco-ipsec-flow-monitor.inc.php");
    include("includes/polling/cisco-remote-access-monitor.inc.php");

    unset($update);
    unset($seperator);

    if ($serial && $serial != $device['serial'])
    {
      $poll_update .= $poll_separator . "`serial` = '".mres($serial)."'";
      $poll_separator = ", ";
      log_event("Serial -> $serial", $device, 'system');
    }

    if ($sysContact && $sysContact != $device['sysContact'])
    {
      $poll_update .= $poll_separator . "`sysContact` = '".mres($sysContact)."'";
      $poll_separator = ", ";
      log_event("Contact -> $sysContact", $device, 'system');
    }

    if ($sysName && $sysName != $device['sysName'])
    {
      $poll_update .= $poll_separator . "`sysName` = '$sysName'";
      $poll_separator = ", ";
      log_event("sysName -> $sysName", $device, 'system');
    }

    if ($sysDescr && $sysDescr != $device['sysDescr'])
    {
      $poll_update .= $poll_separator . "`sysDescr` = '$sysDescr'";
      $poll_separator = ", ";
      log_event("sysDescr -> $sysDescr", $device, 'system');
    }

    if ($sysLocation && $device['location'] != $sysLocation)
    {
      if (!get_dev_attrib($device,'override_sysLocation_bool'))
      {
        $poll_update .= $poll_separator . "`location` = '$sysLocation'";
        $poll_separator = ", ";
      }
      log_event("Location -> $sysLocation", $device, 'system');
    }

    if ($version && $device['version'] != $version)
    {
      $poll_update .= $poll_separator . "`version` = '$version'";
      $poll_separator = ", ";
      log_event("OS Version -> $version", $device, 'system');
    }

    if ($features != $device['features'])
    {
      $poll_update .= $poll_separator . "`features` = '$features'";
      $poll_separator = ", ";
      log_event("OS Features -> $features", $device, 'system');
    }

    if ($hardware && $hardware != $device['hardware'])
    {
      $poll_update .= $poll_separator . "`hardware` = '$hardware'";
      $poll_separator = ", ";
      log_event("Hardware -> $hardware", $device, 'system');
    }

    $poll_update .= $poll_separator . "`last_polled` = NOW()";
    $poll_separator = ", ";
    $polled_devices++;
    echo("\n");

  }

  ## FIXME EVENTLOGGING
  ### This code cycles through the graphs already known in the database and the ones we've defined as being polled here
  ### If there any don't match, they're added/deleted from the database.
  ### Ideally we should hold graphs for xx days/weeks/polls so that we don't needlessly hide information.

  $query = mysql_query("SELECT `graph` FROM `device_graphs` WHERE `device_id` = '".$device['device_id']."'");
  while ($graph = mysql_fetch_array($query))
  {
    if (!isset($graphs[$graph[0]]))
    {
      mysql_query("DELETE FROM `device_graphs` WHERE `device_id` = '".$device['device_id']."' AND `graph` = '".$graph[0]."'");
    } else {
      $oldgraphs[$graph[0]] = TRUE;
    }
  }

  foreach ($graphs as $graph => $value)
  {
    if (!isset($oldgraphs[$graph]))
    {
      mysql_query("INSERT INTO `device_graphs` (`device_id`, `graph`) VALUES ('".$device['device_id']."','".$graph."')");
    }
  }

  $device_end = utime(); $device_run = $device_end - $device_start; $device_time = substr($device_run, 0, 5);
  $poll_update .= $poll_separator . "`last_polled_timetaken` = '$device_time'";
  #echo("$device_end - $device_start; $device_time $device_run");
  echo("Polled in $device_time seconds\n");

  $poll_update_query  = "UPDATE `devices` SET ";
  $poll_update_query .= $poll_update;
  $poll_update_query .= " WHERE `device_id` = '" . $device['device_id'] . "'";
  if ($debug) { echo("Updating " . $device['hostname'] . " - $poll_update_query \n"); }
  if (!mysql_query($poll_update_query))
  {
    echo "ERROR: " . mysql_error() . "\nSQL: $poll_update_query\n";
  }
  if (mysql_affected_rows() == "1") { echo("UPDATED!\n"); } else { echo("NOT UPDATED!\n"); }

  unset($storage_cache); // Clear cache of hrStorage ** MAYBE FIXME? **
  unset($cache); // Clear cache (unify all things here?)
}

$poller_end = utime(); $poller_run = $poller_end - $poller_start; $poller_time = substr($poller_run, 0, 5);

if ($polled_devices)
{
  mysql_query("INSERT INTO `perf_times` (`type`, `doing`, `start`, `duration`, `devices`)
                               VALUES ('poll', '$doing', '$poller_start', '$poller_time', '$polled_devices')");
}

$string = $argv[0] . " $doing " .  date("F j, Y, G:i") . " - $polled_devices devices polled in $poller_time secs";
if ($debug) echo("$string\n");

logfile($string);

unset($config); ### Remove this for testing

#print_r(get_defined_vars());

?>