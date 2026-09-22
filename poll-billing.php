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

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use App\Models\BillPortCounter;
use Illuminate\Support\Facades\DB;
use LibreNMS\Billing;
use LibreNMS\Data\Store\Datastore;
use LibreNMS\Util\Debug;

$init_modules = [];
require __DIR__ . '/includes/init.php';

if (isset($argv[1]) && is_numeric($argv[1])) {
    // allow old cli style
    $options = ['b' => $argv[1]];
} else {
    $options = getopt('fdb:');
}

Debug::set(isset($options['d']));
Datastore::init();

$scheduler = LibrenmsConfig::get('schedule_type.billing');
if (! isset($options['f']) && $scheduler != 'legacy' && $scheduler != 'cron') {
    Log::debug('Billing is not enabled for cron scheduling. Add the -f command argument if you want to force this command to run.');
    exit(0);
}

$poller_start = microtime(true);
Log::info("Starting Bill Polling Session ... \n");

$query = Bill::with([
    'ports' => fn ($q) => $q->whereIn('ifOperStatus', ['up', 'dormant'])
        ->select(['ports.port_id', 'device_id', 'ifName', 'ifDescr', 'ifIndex', 'ifSpeed']),
    'portCounters',
    'data' => fn ($q) => $q->latest('timestamp')->limit(1),
])->when($options['b'] ?? null, fn ($q, $bill_id) => $q->where('bill_id', $bill_id));

$poller_group = (LibrenmsConfig::get('distributed_poller') && LibrenmsConfig::get('distributed_billing')) ? LibrenmsConfig::get('distributed_poller_group') : null;

foreach ($query->get(['bill_id', 'bill_name']) as $bill) {
    Log::info('Bill : ' . $bill->bill_name);
    $bill_id = $bill->bill_id;

    $now = DB::scalar('SELECT NOW()');
    $delta = 0;
    $in_delta = 0;
    $out_delta = 0;
    $counterUpdates = [];
    $polledPortCount = 0;
    $lastCountersByPort = $bill->portCounters->keyBy('port_id');

    foreach ($bill->ports as $port) {
        $device = DeviceCache::get($port->device_id);

        if ($device->disabled || ($poller_group && $device->poller_group != $poller_group)) {
            continue;
        }

        $polledPortCount++;
        Log::info("  Polling $port->ifName ($port->ifDescr) on $device->hostname");

        $in_measurement = Billing::getValue($port->device_id, $port->ifIndex, 'In');
        $out_measurement = Billing::getValue($port->device_id, $port->ifIndex, 'Out');

        if ($in_measurement === null || $out_measurement === null) {
            Log::error("WATCH out! - Wrong counters. Table 'bill_port_counters' not updated");
            continue;
        }

        $last_counters = $lastCountersByPort->get($port->port_id);
        if ($last_counters !== null) {
            $tmp_period = DB::scalar('SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - UNIX_TIMESTAMP(?)', [$last_counters->timestamp]);

            if ($port->ifSpeed > 0 && Billing::calculateBitrate($in_measurement, $last_counters->in_counter, $tmp_period) > $port->ifSpeed) {
                $in_delta = $last_counters->in_delta;
            } elseif ($in_measurement >= $last_counters->in_counter) {
                $in_delta = ($in_measurement - $last_counters->in_counter);
            } else {
                $in_delta = $last_counters->in_delta;
            }

            if ($port->ifSpeed > 0 && Billing::calculateBitrate($out_measurement, $last_counters->out_counter, $tmp_period) > $port->ifSpeed) {
                $out_delta = $last_counters->out_delta;
            } elseif ($out_measurement >= $last_counters->out_counter) {
                $out_delta = ($out_measurement - $last_counters->out_counter);
            } else {
                $out_delta = $last_counters->out_delta;
            }
        } else {
            $in_delta = 0;
            $out_delta = 0;
        }
        //////////////////////////////////CountersValidation$DB-Update
        //For debugging
        Log::debug("****$now: " . $bill->bill_name . ' Billing DB SNMP counters received.');
        Log::debug('in_measurement: ' . $in_measurement . '  out_measurement: ' . $out_measurement . "\nThe data types are. in_measurement:" . gettype($in_measurement) . ' and out_measurement: ' . gettype($out_measurement));
        Log::debug('IN_delta: ' . $in_delta . ' OUT_delta: ' . $out_delta . "\nLast_IN_delta: " . ($last_counters->in_delta ?? '') . ' last_OUT_delta: ' . ($last_counters->out_delta ?? ''));

        Log::debug("Nice, valid counters 'in/out_measurement', lets use them");
        $counterUpdates[] = [
            'port_id' => $port->port_id,
            'bill_id' => $bill_id,
            'timestamp' => $now,
            'in_counter' => $in_measurement,
            'out_counter' => $out_measurement,
            'in_delta' => (int) $in_delta,
            'out_delta' => (int) $out_delta,
        ];
        ////////////////////////////////EndCountersValidation&DB-Update
        $delta = ($delta + $in_delta + $out_delta);
        $in_delta = ($in_delta + $in_delta);
        $out_delta = ($out_delta + $out_delta);
    }//end foreach

    if (! empty($counterUpdates)) {
        BillPortCounter::upsert(
            $counterUpdates,
            ['port_id', 'bill_id'],
            ['timestamp', 'in_counter', 'out_counter', 'in_delta', 'out_delta']
        );
    }

    $last_data = $bill->data->first();

    if ($last_data !== null) {
        $prev_delta = $last_data->delta;
        $prev_in_delta = $last_data->in_delta;
        $prev_out_delta = $last_data->out_delta;
        $prev_timestamp = $last_data->timestamp;
        $period = DB::scalar('SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - UNIX_TIMESTAMP(?)', [$prev_timestamp]);
    } else {
        $prev_delta = 0;
        $period = 0;
        $prev_in_delta = 0;
        $prev_out_delta = 0;
    }

    if ($delta < 0) {
        $delta = $prev_delta;
        $in_delta = $prev_in_delta;
        $out_delta = $prev_out_delta;
    }

    if (! empty($period) && $period < 0) {
        Log::debug("BILLING: negative period! id:$bill_id period:$period delta:$delta in_delta:$in_delta out_delta:$out_delta");
    } elseif ($polledPortCount > 0) {
        // If no ports are part of this bill then don't insert a zero value entry
        $bill->data()->create([
            'timestamp' => $now,
            'period' => $period,
            'delta' => $delta,
            'in_delta' => $in_delta,
            'out_delta' => $out_delta,
        ]);
    }
}//end CollectData()

$poller_end = microtime(true);
$poller_run = $poller_end - $poller_start;
$poller_time = round($poller_run, 3);

if ($poller_time > 300) {
    Log::warning("BILLING: polling took longer than 5 minutes ($poller_time seconds)!");
}
Log::info("Completed in $poller_time sec");

app('Datastore')->terminate();
