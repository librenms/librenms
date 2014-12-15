#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

chdir(dirname($argv[0]));

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.php");
include("includes/polling/functions.inc.php");
include("includes/alerts.inc.php");

$poller_start = utime();
echo($config['project_name_version']." Poller\n\n");

$options = getopt("h:m:i:n:r::d::a::");

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
            WHERE MOD(temp.rownum, '.mres($options['i']).') = '.mres($options['n']).';';
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
  echo("-r                                           Do not create or update RRDs\n");
  echo("-d                                           Enable debugging output\n");
  echo("-m                                           Specify module(s) to be run\n");
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

if (isset($options['r']))
{
  $config['norrd'] = TRUE;
}

rrdtool_pipe_open($rrd_process, $rrd_pipes);

echo("Starting polling run:\n\n");
$polled_devices = 0;
if (!isset($query))
{
  $query = "SELECT `device_id` FROM `devices` WHERE `disabled` = 0 $where ORDER BY `device_id` ASC";
}

foreach (dbFetch($query) as $device)
{
  $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = '".$device['device_id']."'");
  poll_device($device, $options);
  RunRules($device['device_id']);
  echo "\r\n";
  $polled_devices++;
}

$poller_end = utime(); $poller_run = $poller_end - $poller_start; $poller_time = substr($poller_run, 0, 5);

if ($polled_devices)
{
  dbInsert(array('type' => 'poll', 'doing' => $doing, 'start' => $poller_start, 'duration' => $poller_time, 'devices' => $polled_devices ), 'perf_times');
}

$string = $argv[0] . " $doing " .  date($config['dateformat']['compact']) . " - $polled_devices devices polled in $poller_time secs";
if ($debug) { echo("$string\n"); }

echo("\n" .
     'MySQL: Cell['.($db_stats['fetchcell']+0).'/'.round($db_stats['fetchcell_sec']+0,2).'s]'.
            ' Row['   .($db_stats['fetchrow']+0). '/'.round($db_stats['fetchrow_sec']+0,2).'s]'.
           ' Rows['   .($db_stats['fetchrows']+0).'/'.round($db_stats['fetchrows_sec']+0,2).'s]'.
         ' Column['.($db_stats['fetchcol']+0). '/'.round($db_stats['fetchcol_sec']+0,2).'s]'.
         ' Update['   .($db_stats['update']+0).'/'.round($db_stats['update_sec']+0,2).'s]'.
         ' Insert['.($db_stats['insert']+0). '/'.round($db_stats['insert_sec']+0,2).'s]'.
         ' Delete['.($db_stats['delete']+0). '/'.round($db_stats['delete_sec']+0,2).'s]');

echo("\n");

logfile($string);
rrdtool_pipe_close($rrd_process, $rrd_pipes);
unset($config); // Remove this for testing

#print_r(get_defined_vars());

?>
