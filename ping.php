#!/usr/bin/env php
<?php

use App\Models\Device;
use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\RRD\RrdDefinition;
use Symfony\Component\Process\Process;

$options = getopt('d');
$ping_start = microtime(true);

$init_modules = ['alerts', 'eloquent'];
require __DIR__ . '/includes/init.php';

set_debug(isset($options['d']));

if ($config['noinfluxdb'] !== true && $config['influxdb']['enable'] === true) {
    $influxdb = influxdb_connect();
} else {
    $influxdb = false;
}

rrdtool_initialize();

/** @var \Illuminate\Database\Eloquent\Collection $devices List of devices keyed by hostname*/
$devices = Device::canPing()
    ->select(['devices.device_id', 'hostname', 'status', 'status_reason', 'last_ping', 'last_ping_timetaken'])
    ->orderBy('max_depth')
    ->get()
    ->keyBy('hostname');

// rrd vars
$rrd_step = Config::get('ping_rrd_step', Config::get('rrd.step', 300));
$rrd_def = RrdDefinition::make()->addDataset('ping', 'GAUGE', 0, 65535, $rrd_step * 2);
$tags = ['rrd_def' => $rrd_def, 'rrd_step' => $rrd_step];

$timeout = Config::get('fping_options.timeout', 500); // must be smaller than period
$retries = Config::get('fping_options.retries', 2);  // how many retries on failure

$cmd = ['fping', '-f', '-', '-e', '-t', $timeout, '-r', $retries];

$fping = new Process($cmd, null, null, null, 300);
d_echo($fping->getCommandLine() . PHP_EOL);

// send hostnames to stdin to avoid overflowing cli length limits
$fping->setInput($devices->keys()->implode(PHP_EOL));
$fping->start();

foreach ($fping as $type => $line) {
    d_echo($line);

    if ($fping::ERR === $type) {
        // don't process stderr
        continue;
    }

    $res = preg_match(
        '/^(?<hostname>[^\s]+) is (?<status>alive|unreachable)(?: \((?<rtt>[\d.]+) ms\))?/',
        $line,
        $captured
    );

    if ($res) {
        /** @var Device $device */
        $device = $devices->get($captured['hostname']);

        if ($device) {
            // mark up only if snmp is not down too
            $device->status = ($captured['status'] == 'alive' && $device->status_reason != 'snmp');
            $device->last_ping = Carbon::now();
            $device->last_ping_timetaken = isset($captured['rtt']) ? $captured['rtt'] : 0;

            if ($device->isDirty('status')) {
                // if changed, update reason
                $device->status_reason = $device->status ? '' : 'icmp';
                $type = $device->status ? 'up' : 'down';
                log_event('Device status changed to ' . ucfirst($type) . " from icmp check.", $device->toArray(), $type);

                echo "Device $device->hostname changed status to $type, running alerts\n";
                RunRules($device->device_id);
            }
            $device->save(); // only saves if needed (which is every time because of last_ping)

            // add data to rrd
            data_update($device->toArray(), 'ping-perf', $tags, ['ping' => $device->last_ping_timetaken]);
        }
    }
}

rrdtool_close();

printf("Pinged %s devices in %.2fs\n", $devices->count(), microtime(true) - $ping_start);
