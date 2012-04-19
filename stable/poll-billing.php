#!/usr/bin/env php
<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 *
 * @package    observium
 * @subpackage billing
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

chdir(dirname($argv[0]));

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

$options = getopt("d");

if (isset($options['d'])) { $debug = TRUE; }

$iter = "0";

rrdtool_pipe_open($rrd_process, $rrd_pipes);

echo("Observium Billing Poller v".$config['version']."\n\n");

foreach (dbFetchRows("SELECT * FROM `bills`") as $bill_data)
{
  echo("Bill : ".$bill_data['bill_name']."\n");

  # replace old bill_gb with bill_quota (we're now storing bytes, not gigabytes)

  if ($bill_data['bill_type'] == "quota" && !is_numeric($bill_data['bill_quota']))
  {
    $bill_data['bill_quota'] = $bill_data['bill_gb'] * $config['billing']['base'] * $config['billing']['base'];
    dbUpdate(array('bill_quota' => $bill_data['bill_quota']), 'bills', '`bill_id` = ?', array($bill_data['bill_id']));
    echo("Quota -> ".$bill_data['bill_quota']);
  }

  CollectData($bill_data['bill_id']);
  $iter++;
}

function CollectData($bill_id)
{
  foreach (dbFetchRows("SELECT * FROM `bill_ports` as P, `ports` as I, `devices` as D WHERE P.bill_id=? AND I.interface_id = P.port_id AND D.device_id = I.device_id", array($bill_id)) as $port_data)
  {
    $port_id = $port_data['port_id'];
    $host    = $port_data['hostname'];
    $port    = $port_data['port'];

    echo("\nPolling ".$port_data['ifDescr']." on ".$port_data['hostname']."\n");

    $port_data['in_measurement'] = getValue($port_data['hostname'], $port_data['port'], $port_data['ifIndex'], "In");
    $port_data['out_measurement'] = getValue($port_data['hostname'], $port_data['port'], $port_data['ifIndex'], "Out");

    $now = dbFetchCell("SELECT NOW()");

    if($debug)
    {
      echo("Current at:".$now." In:".$port_data['in_measurement']." Out:".$port_data['out_measurement']."\n");
    }
    
    $last_in  = getLastPortCounter($port_id,in);
    $last_out = getLastPortCounter($port_id,out);
    $last_bill = getLastMeasurement($bill_id);

    if($debug)
    {
      print_r($last_in);
      print_r($last_out);
      print_r($last_bill);
    }

    if ($last_in['state'] == "ok")
    {
      $port_data['last_in_measurement'] = $last_in[counter];
      $port_data['last_in_delta'] = $last_in[delta];
      if ($port_data['in_measurement'] > $port_data['last_in_measurement'])
      {
        $port_data['in_delta'] = $port_data['in_measurement'] - $port_data['last_in_measurement'];
      } else {
        $port_data['in_delta'] = $port_data['last_in_delta'];
      }
    } else {
      $port_data['in_delta'] = '0';
    }
    dbInsert(array('port_id' => $port_id, 'timestamp' => $now, 'counter' => $port_data['in_measurement'], 'delta' => $port_data['in_delta']), 'port_in_measurements');

    if ($last_out[state] == "ok")
    {
      $port_data['last_out_measurement'] = $last_out[counter];
      $port_data['last_out_delta'] = $last_out[delta];
      if ($port_data['out_measurement'] > $port_data['last_out_measurement'])
      {
        $port_data['out_delta'] = $port_data['out_measurement'] - $port_data['last_out_measurement'];
      } else {
        $port_data['out_delta'] = $port_data['last_out_delta'];
      }
    } else {
      $port_data['out_delta'] = '0';
    }
    dbInsert(array('port_id' => $port_id, 'timestamp' => $now, 'counter' => $port_data['out_measurement'], 'delta' => $port_data['out_delta']), 'port_out_measurements');

    $delta = $delta + $port_data['in_delta'] + $port_data['out_delta'];
    $in_delta = $in_delta + $port_data['in_delta'];
    $out_delta = $out_delta + $port_data['out_delta'];

  }

  if ($last_bill[state] == "ok")
  {
    $prev_delta     = $last_bill[delta];
    $prev_in_delta  = $last_bill[in_delta];
    $prev_out_delta = $last_bill[out_delta];
    $prev_timestamp = $last_bill[timestamp];
    $period = dbFetchCell("SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - UNIX_TIMESTAMP('".mres($prev_timestamp)."')");
  } else {
    $prev_delta = '0';
    $period   = '0';
    $prev_in_delta =  '0';
    $prev_out_delta =  '0';
  }

  ## Hack. If the counters have gone backwards, we assume the delta is the same as the previous.
  if ($delta < '0')
  {
    $delta = $prev_delta;
    $in_delta = $prev_in_delta;
    $out_delta = $prev_out_delta;

  }

  if ($period < "0") {
    logfile("BILLING: negative period! id:$bill_id period:$period delta:$delta in_delta:$in_delta out_delta:$out_delta");
  } else {
    dbInsert(array('bill_id' => $bill_id, 'timestamp' => $now, 'period' => $period, 'delta' => $delta, 'in_delta' => $in_delta, 'out_delta' => $out_delta), 'bill_data');
  }
}

if ($argv[1]) { CollectData($argv[1]); }

rrdtool_pipe_close($rrd_process, $rrd_pipes);

?>
