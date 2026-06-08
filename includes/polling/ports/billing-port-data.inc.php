<?php

/*
 * Emit per-port billing shadow samples from the normal port poller.
 *
 * This is intentionally append-only and non-authoritative.
 * Production billing still uses poll-billing.php and bill_data.
 */

use Illuminate\Support\Facades\DB;

if (empty($port_id) || empty($device['device_id'])) {
    return;
}

if (empty($polled_period) || $polled_period < 1) {
    return;
}

$in_delta = (int) ($current_port_stats['ifInOctets_diff'] ?? 0);
$out_delta = (int) ($current_port_stats['ifOutOctets_diff'] ?? 0);

if (! array_key_exists('ifInOctets', $this_port) || ! array_key_exists('ifOutOctets', $this_port)) {
    return;
}

$in_counter = (int) $this_port['ifInOctets'];
$out_counter = (int) $this_port['ifOutOctets'];
if ($in_delta < 0 || $out_delta < 0) {
    return;
}

$bill_ids = DB::table('bill_ports')
    ->where('port_id', $port_id)
    ->pluck('bill_id');

if ($bill_ids->isEmpty()) {
    return;
}

$rows = [];
$timestamp = date('Y-m-d H:i:s', $polled);

foreach ($bill_ids as $bill_id) {
    $rows[] = [
        'bill_id' => (int) $bill_id,
        'port_id' => (int) $port_id,
        'device_id' => (int) $device['device_id'],
        'timestamp' => $timestamp,
        'poll_period' => (int) $polled_period,
        'in_delta' => $in_delta,
        'out_delta' => $out_delta,
        'in_counter' => $in_counter,
        'out_counter' => $out_counter,
        'processed' => 0,
    ];
}

DB::table('bill_port_data')->insert($rows);
