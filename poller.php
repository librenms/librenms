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
include("includes/polling/functions.inc.php");

$poller_start = utime();
echo("Observium Poller v".$config['version']."\n\n");

$options = getopt("h:m:i:n:d::a::");

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
  $where = true; // FIXME
  $query = 'SELECT `device_id` FROM (SELECT @rownum :=0) r,
              (
                SELECT @rownum := @rownum +1 AS rownum, `device_id`
                FROM `devices`
                WHERE `disabled` = 0 
                ORDER BY `device_id` ASC
              ) temp
            WHERE MOD(temp.rownum, '.$options['i'].') = '.$options['n'].';';
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
  echo("Debugging and testing options:\n");
  echo("-d                                           Enable debugging output\n");
  echo("-m                                           Specify single module to be run\n");
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
if(!isset($query))
{
  $device_query = mysql_query("SELECT `device_id` FROM `devices` WHERE `disabled` = 0 $where  ORDER BY `device_id` ASC");
} else {
  $device_query = mysql_query($query);
}
print mysql_error();

while ($device = mysql_fetch_assoc($device_query))
{
  $device = mysql_fetch_assoc(mysql_query("SELECT * FROM `devices` WHERE `device_id` = '".$device['device_id']."'"));

  poll_device($device, $options);

}


function poll_device($device, $options) {

  global $config;

  $attribs = get_dev_attribs($device['device_id']);

  $status = 0; unset($array);
  $device_start = utime();  // Start counting device poll time

  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");
  if ($config['os'][$device['os']]['group'])
  {
    $device['os_group'] = $config['os'][$device['os']]['group'];
    echo("(".$device['os_group'].")");
  }
  echo("\n");

  unset($poll_update); unset($poll_update_query); unset($poll_separator); 

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

    if ($options['m'])
    {
      if (is_file("includes/polling/".$options['m'].".inc.php"))
      {
        include("includes/polling/".$options['m'].".inc.php");
      }
    } else {
      foreach($config['poller_modules'] as $module => $module_status)
      {
        if ($attribs['poll_'.$module] || ( $module_status && !isset($attribs['poll_'.$module])))
        {
          include('includes/polling/'.$module.'.inc.php');
        } elseif (isset($attribs['poll_'.$module]) && $attribs['poll_'.$module] == "0") {
          echo("Module [ $module ] disabled on host.\n");
        } else {
          echo("Module [ $module ] disabled globally.\n");
        }
      }
    }

    $device_end = utime(); $device_run = $device_end - $device_start; $device_time = substr($device_run, 0, 5);
    $device['db_update'] = " `last_polled` = NOW() " . $device['db_update'];
    $device['db_update'] .= ", `last_polled_timetaken` = '$device_time'";
    #echo("$device_end - $device_start; $device_time $device_run");
    echo("Polled in $device_time seconds\n");

    $device['db_update_query']  = "UPDATE `devices` SET ";
    $device['db_update_query'] .= $device['db_update'];
    $device['db_update_query'] .= " WHERE `device_id` = '" . $device['device_id'] . "'";
    if ($debug) { echo("Updating " . $device['hostname'] . " - ".$device['db_update_query']." \n"); }
    if (!mysql_query($device['db_update_query']))
    {
    echo("ERROR: " . mysql_error() . "\nSQL: ".$device['db_update_query']."\n");
    }
    if (mysql_affected_rows() == "1") { echo("UPDATED!\n"); } else { echo("NOT UPDATED!\n"); }

    unset($storage_cache); // Clear cache of hrStorage ** MAYBE FIXME? **
    unset($cache); // Clear cache (unify all things here?)
  }

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
