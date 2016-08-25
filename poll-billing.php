#!/usr/bin/env php
<?php

/*
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage billing
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

chdir(dirname($argv[0]));

// FIXME - implement cli switches, debugging, etc.
require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';
require 'includes/functions.php';

$iter = '0';

rrdtool_initialize();

$poller_start = microtime(true);
echo "Starting Polling Session ... \n\n";

// Wait for schema update, as running during update can break update
$dbVersion = dbFetchCell('SELECT version FROM dbSchema');
if ($dbVersion < 107) {
    logfile("BILLING: Cannot continue until dbSchema update to >= 107 is complete");
    exit(1);
}

foreach (dbFetchRows('SELECT * FROM `bills`') as $bill_data) {
    echo 'Bill : '.$bill_data['bill_name']."\n";

    // replace old bill_gb with bill_quota (we're now storing bytes, not gigabytes)
    if ($bill_data['bill_type'] == 'quota' && !is_numeric($bill_data['bill_quota'])) {
        $bill_data['bill_quota'] = ($bill_data['bill_gb'] * $config['billing']['base'] * $config['billing']['base']);
        dbUpdate(array('bill_quota' => $bill_data['bill_quota']), 'bills', '`bill_id` = ?', array($bill_data['bill_id']));
        echo 'Quota -> '.$bill_data['bill_quota'];
    }

    CollectData($bill_data['bill_id']);
    $iter++;
}


function CollectData($bill_id) {
    $port_list = dbFetchRows('SELECT * FROM `bill_ports` as P, `ports` as I, `devices` as D WHERE P.bill_id=? AND I.port_id = P.port_id AND D.device_id = I.device_id', array($bill_id));

    $now = dbFetchCell('SELECT NOW()');
    $delta = 0;
    $in_delta = 0;
    $out_delta = 0;
    foreach ($port_list as $port_data) {
        $port_id = $port_data['port_id'];
        $host    = $port_data['hostname'];
        $port    = $port_data['port'];

        echo "  Polling ${port_data['ifName']} (${port_data['ifDescr']}) on ${port_data['hostname']}\n";

        $port_data['in_measurement']  = getValue($port_data['hostname'], $port_data['port'], $port_data['ifIndex'], 'In');
        $port_data['out_measurement'] = getValue($port_data['hostname'], $port_data['port'], $port_data['ifIndex'], 'Out');

        $last_counters = getLastPortCounter($port_id, $bill_id);
        if ($last_counters['state'] == 'ok') {
            $port_data['last_in_measurement']  = $last_counters[in_counter];
            $port_data['last_in_delta']        = $last_counters[in_delta];
            $port_data['last_out_measurement'] = $last_counters[out_counter];
            $port_data['last_out_delta']       = $last_counters[out_delta];

            $tmp_period = dbFetchCell("SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - UNIX_TIMESTAMP('".mres($last_counters['timestamp'])."')");

            if ($port_data['ifSpeed'] > 0 && (delta_to_bits($port_data['in_measurement'], $tmp_period)-delta_to_bits($port_data['last_in_measurement'], $tmp_period)) > $port_data['ifSpeed']) {
                $port_data['in_delta'] = $port_data['last_in_delta'];
            }
            elseif ($port_data['in_measurement'] >= $port_data['last_in_measurement']) {
                $port_data['in_delta'] = ($port_data['in_measurement'] - $port_data['last_in_measurement']);
            }
            else {
                $port_data['in_delta'] = $port_data['last_in_delta'];
            }
            
            if ($port_data['ifSpeed'] > 0 && (delta_to_bits($port_data['out_measurement'], $tmp_period)-delta_to_bits($port_data['last_out_measurement'], $tmp_period)) > $port_data['ifSpeed']) {
                $port_data['out_delta'] = $port_data['last_out_delta'];
            }
            elseif ($port_data['out_measurement'] >= $port_data['last_out_measurement']) {
                $port_data['out_delta'] = ($port_data['out_measurement'] - $port_data['last_out_measurement']);
            }
            else {
                $port_data['out_delta'] = $port_data['last_out_delta'];
            }
        }
        else {
            $port_data['in_delta'] = '0';
            $port_data['out_delta'] = '0';
        }

        $fields = array('timestamp' => $now, 'in_counter' => $port_data['in_measurement'], 'out_counter' => $port_data['out_measurement'], 'in_delta' => $port_data['in_delta'], 'out_delta' => $port_data['out_delta']);
        if (dbUpdate($fields, 'bill_port_counters', "`port_id`='" . mres($port_id) . "' AND `bill_id`='$bill_id'") == 0) {
            $fields['bill_id'] = $bill_id;
            $fields['port_id'] = $port_id;
            dbInsert($fields, 'bill_port_counters');
        }

        $delta     = ($delta + $port_data['in_delta'] + $port_data['out_delta']);
        $in_delta  = ($in_delta + $port_data['in_delta']);
        $out_delta = ($out_delta + $port_data['out_delta']);
    }//end foreach

    $last_data = getLastMeasurement($bill_id);

    if ($last_data[state] == 'ok') {
        $prev_delta     = $last_data[delta];
        $prev_in_delta  = $last_data[in_delta];
        $prev_out_delta = $last_data[out_delta];
        $prev_timestamp = $last_data[timestamp];
        $period         = dbFetchCell("SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - UNIX_TIMESTAMP('".mres($prev_timestamp)."')");
    }
    else {
        $prev_delta     = '0';
        $period         = '0';
        $prev_in_delta  = '0';
        $prev_out_delta = '0';
    }

    if ($delta < '0') {
        $delta     = $prev_delta;
        $in_delta  = $prev_in_delta;
        $out_delta = $prev_out_delta;
    }

    if (!empty($period) && $period < '0') {
        logfile("BILLING: negative period! id:$bill_id period:$period delta:$delta in_delta:$in_delta out_delta:$out_delta");
    }
    else {
        dbInsert(array('bill_id' => $bill_id, 'timestamp' => $now, 'period' => $period, 'delta' => $delta, 'in_delta' => $in_delta, 'out_delta' => $out_delta), 'bill_data');
    }

}//end CollectData()


if ($argv[1]) {
    CollectData($argv[1]);
}

$poller_end  = microtime(true);
$poller_run  = ($poller_end - $poller_start);
$poller_time = substr($poller_run, 0, 5);

dbInsert(array('type' => 'pollbill', 'doing' => $doing, 'start' => $poller_start, 'duration' => $poller_time, 'devices' => 0, 'poller' => $config['distributed_poller_name'] ), 'perf_times');
if ($poller_time > 300) {
    logfile("BILLING: polling took longer than 5 minutes ($poller_time seconds)!");
}
echo "\nCompleted in $poller_time sec\n";

rrdtool_close();
