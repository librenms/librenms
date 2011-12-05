#!/usr/bin/env php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

$iter = "0";

rrdtool_pipe_open($rrd_process, $rrd_pipes);

echo("Starting Polling Session ... \n\n");

foreach (dbFetchRows("SELECT * FROM `bills`") as $bill_data)
{
  echo("Bill : ".$bill_data['bill_name']."\n");
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

    $last_data = getLastPortCounter($port_id,in);
    if ($last_data['state'] == "ok")
    {
      $port_data['last_in_measurement'] = $last_data[counter];
      $port_data['last_in_delta'] = $last_data[delta];
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

    $last_data = getLastPortCounter($port_id,out);
    if ($last_data[state] == "ok")
    {
      $port_data['last_out_measurement'] = $last_data[counter];
      $port_data['last_out_delta'] = $last_data[delta];
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
  $last_data = getLastMeasurement($bill_id);

  if ($last_data[state] == "ok")
  {
    $prev_delta     = $last_data[delta];
    $prev_in_delta  = $last_data[in_delta];
    $prev_out_delta = $last_data[out_delta];
    $prev_timestamp = $last_data[timestamp];
    $period = dbFetchCell("SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - UNIX_TIMESTAMP('".mres($prev_timestamp)."')");
  } else {
    $prev_delta = '0';
    $period   = '0';
    $prev_in_delta =  '0';
    $prev_out_delta =  '0';
  }

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
