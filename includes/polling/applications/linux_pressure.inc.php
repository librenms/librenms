<?php

use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'linux_pressure';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    Log::info($name . ':' . $e->getCode() . ':' . $e->getMessage());
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

// avg10, avg60, and avg300 are the percent of that many seconds spent stalled.
// total is a counter of microseconds spent stalled.
$rrd_def = RrdDefinition::make()
    ->addDataset('avg10', 'GAUGE', 0, 100)
    ->addDataset('avg60', 'GAUGE', 0, 100)
    ->addDataset('avg300', 'GAUGE', 0, 100)
    ->addDataset('total', 'DERIVE', 0);

$metrics = [];

// One RRD per resource and stall type as not every kernel reports all of them.
// irq requires 6.1+ and only has full, while cpu only has full on 5.13+.
foreach (['cpu', 'io', 'memory', 'irq'] as $resource) {
    foreach (['some', 'full'] as $stall_type) {
        $pressure = $returned['data']['system'][$resource][$stall_type] ?? null;
        if (! is_array($pressure)) {
            continue;
        }

        $fields = [
            'avg10' => $pressure['avg10'] ?? null,
            'avg60' => $pressure['avg60'] ?? null,
            'avg300' => $pressure['avg300'] ?? null,
            'total' => $pressure['total'] ?? null,
        ];

        $rrd_name = ['app', $name, $app->app_id, 'system___' . $resource . '___' . $stall_type];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        // total is left out as a raw counter is of no use for alerting
        $metric_prefix = 'system_' . $resource . '_' . $stall_type . '_';
        $metrics[$metric_prefix . 'avg10'] = $fields['avg10'];
        $metrics[$metric_prefix . 'avg60'] = $fields['avg60'];
        $metrics[$metric_prefix . 'avg300'] = $fields['avg300'];
    }
}

update_application($app, 'OK', $metrics);
