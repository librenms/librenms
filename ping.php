#!/usr/bin/env php
<?php

use App\Models\Device;
use App\Models\DevicePerf;
use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\RRD\RrdDefinition;
use Symfony\Component\Process\Process;

$init_modules = [];
require __DIR__ . '/includes/init.php';

\LibreNMS\DB\Eloquent::boot(); // just boot Eloquent

//require __DIR__ . '/bootstrap/autoload.php';

//// boot laravel
//$app = require_once __DIR__ . '/bootstrap/app.php';
//$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
//$kernel->bootstrap();


if ($config['noinfluxdb'] !== true && $config['influxdb']['enable'] === true) {
    $influxdb = influxdb_connect();
} else {
    $influxdb = false;
}

rrdtool_initialize();


/** @var \Illuminate\Database\Eloquent\Builder $query */
$query = Device::canPing()->select(['devices.device_id', 'hostname', 'status', 'status_reason', 'last_ping', 'last_ping_timetaken']);

// TODO sort by device dependencies (graph resolution)

$devices = $query->get()->keyBy('hostname');

$period = max(Config::get('fping_options.interval', 500), 10); // minimum period is 10ms
$timeout = min(Config::get('fping_options.timeout', 500), $period); // must be smaller than period
$count = max(Config::get('fping_options.count', 3), 1);  // minimum count is 1

$cmd = ['fping', '-q', '-f', '-', '-p', $period, '-t', $timeout, '-c', $count];

$fping = new Process($cmd, null, null, null, 300);
d_echo($fping->getCommandLine() . PHP_EOL);

// send hostnames to stdin to avoid overflowing cli length limits
$fping->setInput($devices->keys()->implode(PHP_EOL));
$fping->run();

$output = $fping->getErrorOutput();
d_echo($output);

// rrd vars
$rrd_step = Config::get('ping_rrd_step', Config::get('rrd.step', 300));
$rrd_def = RrdDefinition::make()->addDataset('ping', 'GAUGE', 0, 65535, $rrd_step * 2);
$tags = ['rrd_def' => $rrd_def, 'rrd_step' => $rrd_step];

foreach (explode("\n", $output) as $line) {
    $res = preg_match(
        '#^(?<hostname>[^\s]+) +: xmt/rcv/%loss = (?<xmt>\d+)/(?<rcv>\d+)/(?<loss>\d+)%(, min/avg/max = (?<min>[0-9.]+)/(?<avg>[0-9.]+)/(?<max>[0-9.]+))?$#',
        $line,
        $captured
    );

    if ($res) {
        /** @var Device $device */
        $device = $devices->get($captured['hostname']);

        if ($device) {
            $result = new DevicePerf($captured);
            $device->perf()->save($result);

            // mark down only if all packets were loss
            $device->status = ($result->loss != 100);
            $device->last_ping = Carbon::now();
            $device->last_ping_timetaken = $result->avg;

            if ($device->isDirty('status')) {
                // if changed, update reason
                $device->status_reason = $device->status ? '' : 'icmp';
                $type = $device->status ? 'up' : 'down';
                log_event('Device status changed to ' . ucfirst($type) . " from icmp check.", $device->toArray(), $type);
            }
            $device->save(); // only saves if needed

            // add data to rrd
            data_update($device->toArray(), 'ping-perf', $tags, ['ping' => $result->avg]);

            // TODO check alerts?
        }
    }
}

// delete old perf times (or leave this for daily?)
DevicePerf::query()
    ->where('timestamp', '<', Carbon::now()->subDays(Config::get('device_perf_purge', 7)))
    ->delete();

rrdtool_close();
