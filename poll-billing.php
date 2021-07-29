#!/usr/bin/env php
<?php

/*
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage billing
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use LibreNMS\Data\Store\Datastore;
use LibreNMS\Util\Debug;

$init_modules = [];
require __DIR__ . '/includes/init.php';

if (isset($argv[1]) && is_numeric($argv[1])) {
    // allow old cli style
    $options = ['b' => $argv[1]];
} else {
    $options = getopt('db:');
}

Debug::set(isset($options['d']));
Datastore::init();

// Wait for schema update, as running during update can break update
if (\LibreNMS\DB\Schema::getLegacySchema() < 107) {
    logfile('BILLING: Cannot continue until the database schema update to >= 107 is complete');
    exit(1);
}

$poller_start = microtime(true);
echo "Starting Polling Session ... \n\n";

$query = \LibreNMS\DB\Eloquent::DB()->table('bills');

if (isset($options['b'])) {
    $query->where('bill_id', $options['b']);
}

foreach ($query->get(['bill_id', 'bill_name']) as $bill) {
    echo 'Bill : ' . $bill->bill_name . "\n";
    $bill_id = $bill->bill_id;

    $port_list = dbFetchRows('SELECT * FROM `bill_ports` as P, `ports` as I, `devices` as D WHERE P.bill_id=? AND I.port_id = P.port_id AND D.device_id = I.device_id', [$bill_id]);

    $now = dbFetchCell('SELECT NOW()');
    $delta = 0;
    $in_delta = 0;
    $out_delta = 0;
    foreach ($port_list as $port_data) {
        $port_id = $port_data['port_id'];
        $host = $port_data['hostname'];
        $port = $port_data['port'];

        echo "  Polling ${port_data['ifName']} (${port_data['ifDescr']}) on ${port_data['hostname']}\n";

        $port_data['in_measurement'] = getValue($port_data['hostname'], $port_data['port'], $port_data['ifIndex'], 'In');
        $port_data['out_measurement'] = getValue($port_data['hostname'], $port_data['port'], $port_data['ifIndex'], 'Out');

        $last_counters = getLastPortCounter($port_id, $bill_id);
        if ($last_counters['state'] == 'ok') {
            $port_data['last_in_measurement'] = $last_counters['in_counter'];
            $port_data['last_in_delta'] = $last_counters['in_delta'];
            $port_data['last_out_measurement'] = $last_counters['out_counter'];
            $port_data['last_out_delta'] = $last_counters['out_delta'];

            $tmp_period = dbFetchCell("SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - UNIX_TIMESTAMP('" . $last_counters['timestamp'] . "')");

            if ($port_data['ifSpeed'] > 0 && (delta_to_bits($port_data['in_measurement'], $tmp_period) - delta_to_bits($port_data['last_in_measurement'], $tmp_period)) > $port_data['ifSpeed']) {
                $port_data['in_delta'] = $port_data['last_in_delta'];
            } elseif ($port_data['in_measurement'] >= $port_data['last_in_measurement']) {
                $port_data['in_delta'] = ($port_data['in_measurement'] - $port_data['last_in_measurement']);
            } else {
                $port_data['in_delta'] = $port_data['last_in_delta'];
            }

            if ($port_data['ifSpeed'] > 0 && (delta_to_bits($port_data['out_measurement'], $tmp_period) - delta_to_bits($port_data['last_out_measurement'], $tmp_period)) > $port_data['ifSpeed']) {
                $port_data['out_delta'] = $port_data['last_out_delta'];
            } elseif ($port_data['out_measurement'] >= $port_data['last_out_measurement']) {
                $port_data['out_delta'] = ($port_data['out_measurement'] - $port_data['last_out_measurement']);
            } else {
                $port_data['out_delta'] = $port_data['last_out_delta'];
            }
        } else {
            $port_data['in_delta'] = '0';
            $port_data['out_delta'] = '0';
        }
        //////////////////////////////////CountersValidation$DB-Update
        echo "\nDB SNMP counters received.\n";
        echo ' in_measurement: ',$port_data['in_measurement'],' out_measurement: ',$port_data['out_measurement'],"\n";
        echo ' The data types are --> in_measurement:' . gettype($port_data['in_measurement']) . ' and out_measurement: ' . gettype($port_data['out_measurement']) . "\n";
        //For debugging
        logfile("\n****$now: " . $bill->bill_name . "\nDB SNMP counters received.");
        logfile('in_measurement: ' . $port_data['in_measurement'] . '  out_measurement: ' . $port_data['out_measurement'] . "\nThe data types are. in_measurement:" . gettype($port_data['in_measurement']) . ' and out_measurement: ' . gettype($port_data['out_measurement']));
        logfile('IN_delta: ' . $port_data['in_delta'] . ' OUT_delta: ' . $port_data['out_delta'] . "\nLast_IN_delta: " . $port_data['last_in_delta'] . ' last_OUT_delta: ' . $port_data['last_out_delta']);

        if (is_numeric($port_data['in_measurement']) && is_numeric($port_data['out_measurement'])) {
            echo "Nice, valid counters 'in/out_measurement', lets use them\n";
            logfile("Nice, valid counters 'in/out_measurement', lets use them");
            // NOTE: casting to string for mysqli bug (fixed by mysqlnd)
            $fields = ['timestamp' => $now, 'in_counter' => (string) set_numeric($port_data['in_measurement']), 'out_counter' => (string) set_numeric($port_data['out_measurement']), 'in_delta' => (string) set_numeric($port_data['in_delta']), 'out_delta' => (string) set_numeric($port_data['out_delta'])];
            if (dbUpdate($fields, 'bill_port_counters', "`port_id`='" . $port_id . "' AND `bill_id`='$bill_id'") == 0) {
                $fields['bill_id'] = $bill_id;
                $fields['port_id'] = $port_id;
                dbInsert($fields, 'bill_port_counters');
            }
        } else {
            echo "WATCH out! - Wrong counters. Table 'bill_port_counters' not updated\n";
            logfile("WATCH out! - Wrong counters. Table 'bill_port_counters' not updated");
        }
        ////////////////////////////////EndCountersValidation&DB-Update
        $delta = ($delta + $port_data['in_delta'] + $port_data['out_delta']);
        $in_delta = ($in_delta + $port_data['in_delta']);
        $out_delta = ($out_delta + $port_data['out_delta']);
    }//end foreach

    $last_data = getLastMeasurement($bill_id);

    if ($last_data['state'] == 'ok') {
        $prev_delta = $last_data['delta'];
        $prev_in_delta = $last_data['in_delta'];
        $prev_out_delta = $last_data['out_delta'];
        $prev_timestamp = $last_data['timestamp'];
        $period = dbFetchCell("SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - UNIX_TIMESTAMP('" . $prev_timestamp . "')");
    } else {
        $prev_delta = '0';
        $period = '0';
        $prev_in_delta = '0';
        $prev_out_delta = '0';
    }

    if ($delta < '0') {
        $delta = $prev_delta;
        $in_delta = $prev_in_delta;
        $out_delta = $prev_out_delta;
    }

    if (! empty($period) && $period < '0') {
        logfile("BILLING: negative period! id:$bill_id period:$period delta:$delta in_delta:$in_delta out_delta:$out_delta");
    } else {
        // NOTE: casting to string for mysqli bug (fixed by mysqlnd)
        dbInsert(['bill_id' => $bill_id, 'timestamp' => $now, 'period' => $period, 'delta' => (string) $delta, 'in_delta' => (string) $in_delta, 'out_delta' => (string) $out_delta], 'bill_data');
    }
}//end CollectData()

$poller_end = microtime(true);
$poller_run = ($poller_end - $poller_start);
$poller_time = substr($poller_run, 0, 5);

if ($poller_time > 300) {
    logfile("BILLING: polling took longer than 5 minutes ($poller_time seconds)!");
}
echo "\nCompleted in $poller_time sec\n";

Datastore::terminate();
