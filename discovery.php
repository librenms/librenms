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
include("includes/discovery/functions.inc.php");

$start = utime();
$runtime_stats = array();

### Observium Device Discovery

$options = getopt("h:m:i:n:d::a::q");

if (!isset($options['q']))
{
  echo("Observium v".$config['version']." Discovery\n\n");
}

if (isset($options['h']))
{
  if ($options['h'] == "odd")    { $options['n'] = "1"; $options['i'] = "2"; }
  elseif ($options['h'] == "even") { $options['n'] = "0"; $options['i'] = "2"; }
  elseif ($options['h'] == "all")  { $where = " "; $doing = "all"; }
  elseif ($options['h'] == "new")  { $where = "AND `last_discovered` IS NULL"; $doing = "new"; }
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
}

if (isset($options['i']) && $options['i'] && isset($options['n']))
{
  $where = "AND MOD(device_id,".$options['i'].") = '" . $options['n'] . "'";
  $doing = $options['n'] ."/".$options['i'];
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

if (!$where)
{
  echo("-h <device id> | <device hostname wildcard>  Poll single device\n");
  echo("-h odd                                       Poll odd numbered devices  (same as -i 2 -n 0)\n");
  echo("-h even                                      Poll even numbered devices (same as -i 2 -n 1)\n");
  echo("-h all                                       Poll all devices\n");
  echo("-h new                                       Poll all devices that have not had a discovery run before\n\n");
  echo("-i <instances> -n <number>                   Poll as instance <number> of <instances>\n");
  echo("                                             Instances start at 0. 0-3 for -n 4\n\n");
  echo("\n");
  echo("Debugging and testing options:\n");
  echo("-d                                           Enable debugging output\n");
  echo("-m                                           Specify single module to be run\n");
  echo("\n");
  echo("Invalid arguments!\n");
  exit;
}

if (file_exists('.svn'))
{
  list(,$dbu_rev) = preg_split('/: /',@shell_exec('svn info database-update.sql|grep ^Revision'));

  if ($db_rev = @dbFetchCell("SELECT revision FROM `dbSchema`")) {} else
  {
    $db_rev = 0;
  }

  if ($db_rev+0 < 1223)
  {
    include('upgrade-scripts/fix-events.php'); ## Fix events table (needs to copy some data around, so needs script)
  }

  if ($db_rev+0 < 1656)
  {
    include('upgrade-scripts/fix-port-rrd.php'); ## Rewrites all port RRDs. Nothing will work without this after 1656
  }

  if ($db_rev+0 < 1757)
  {
    include('upgrade-scripts/fix-sensor-rrd.php'); ## Rewrites all sensor RRDs. Nothing will work without this after 1757
  }

  if ($db_rev+0 < 2758)
  {
    include('upgrade-scripts/fix-billing-2757.php'); ## Rewrites all sensor RRDs. Nothing will work without this after 1757
  }

  if ($dbu_rev+0 > $db_rev)
  {
    echo("SVN revision changed.\n");
    if ($db_rev+0 < "1000")
    {
      echo("Running pre-revision 1000 SQL update script...\n");
      shell_exec("scripts/update-sql.php database-update-pre1000.sql");
    }
    if ($db_rev+0 < "1435")
    {
      echo("Running pre-revision 1435 SQL update script...\n");
      shell_exec("scripts/update-sql.php database-update-pre1435.sql");
    }
    if ($db_rev+0 < "2245")
    {
      echo("Running pre-revision 2245 (0.11.5) SQL update script...\n");
      shell_exec("scripts/update-sql.php database-update-pre2245.sql");
    }
    echo("Running development SQL update script to update from r$db_rev to r" . trim($dbu_rev) . "...\n");
    shell_exec("scripts/update-sql.php database-update.sql");
    if ($db_rev == 0)
    {
      dbInsert(array('revision' => $dbu_rev), 'dbSchema');
    }
    else
    {
      dbUpdate(array('revision' => $dbu_rev), 'dbSchema');
    }
  }
}

$discovered_devices = 0;

foreach (dbFetch("SELECT * FROM `devices` WHERE status = 1 AND disabled = 0 $where ORDER BY device_id DESC") as $device)
{
  discover_device($device, $options);
}

$end = utime(); $run = $end - $start;
$proctime = substr($run, 0, 5);

if ($discovered_devices)
{
  dbInsert(array('type' => 'discover', 'doing' => $doing, 'start' => $start, 'duration' => $proctime, 'devices' => $discovered_devices), 'perf_times');
}

$string = $argv[0] . " $doing " .  date("F j, Y, G:i") . " - $discovered_devices devices discovered in $proctime secs";
if ($debug) echo("$string\n");

if(!$options['h'] == "new" && $config['version_check']) {
  include("includes/versioncheck.inc.php");
}

if(!isset($options['q'])) {
  echo('MySQL: Cell['.($db_stats['fetchcell']+0).'/'.round($db_stats['fetchcell_sec']+0,2).'s]'.
            ' Row['.($db_stats['fetchrow']+0). '/'.round($db_stats['fetchrow_sec']+0,2).'s]'.
           ' Rows['.($db_stats['fetchrows']+0).'/'.round($db_stats['fetchrows_sec']+0,2).'s]'.
         ' Column['.($db_stats['fetchcol']+0). '/'.round($db_stats['fetchcol_sec']+0,2).'s]'.
         ' Update['.($db_stats['update']+0).'/'.round($db_stats['update_sec']+0,2).'s]'.
         ' Insert['.($db_stats['insert']+0). '/'.round($db_stats['insert_sec']+0,2).'s]'.
         ' Delete['.($db_stats['delete']+0). '/'.round($db_stats['delete_sec']+0,2).'s]');
  echo("\n");
}

logfile($string);

?>
