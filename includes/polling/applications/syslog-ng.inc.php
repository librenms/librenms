<?php

use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'syslog-ng';

try {
    $data = json_app_get($device, $name)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . "$name:{$e->getCode()}:{$e->getMessage()}" . PHP_EOL;
    update_application($app, "{$e->getCode()}:{$e->getMessage()}");

    return;
}

$metrics = [
    'center_queued_processed' => $data['center_queued_processed'],
    'center_received_processed' => $data['center_received_processed'],
];
$app_data = [
    'global' => [],
    'sources' => [],
];

$center_rrd_def = RrdDefinition::make()
    ->addDataset('queued', 'DERIVE', 0)
    ->addDataset('received', 'DERIVE', 0);

$center_fields = [
    'queued' => $data['center_queued_processed'],
    'received' => $data['center_received_processed'],
];

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id, 'center'],
    'rrd_def' => $center_rrd_def,
];

app('Datastore')->put($device, 'app', $tags, $center_fields);

$counter_rrd_def = RrdDefinition::make()
    ->addDataset('max', 'DERIVE', 0)
    ->addDataset('mean', 'DERIVE', 0)
    ->addDataset('median', 'DERIVE', 0)
    ->addDataset('min', 'DERIVE', 0)
    ->addDataset('mode', 'DERIVE', 0)
    ->addDataset('sum', 'DERIVE', 0);

$gauge_rrd_def = RrdDefinition::make()
    ->addDataset('max', 'GAUGE', 0)
    ->addDataset('mean', 'GAUGE', 0)
    ->addDataset('median', 'GAUGE', 0)
    ->addDataset('min', 'GAUGE', 0)
    ->addDataset('mode', 'GAUGE', 0)
    ->addDataset('sum', 'GAUGE', 0);

$single_counter_rrd_def = RrdDefinition::make()
    ->addDataset('data', 'DERIVE', 0);

$gauge = [
    'batch_size_avg',
    'batch_size_max',
    'memory_usage',
    'msg_size_avg',
];

$counter = [
    'connections',
    'discarded',
    'truncated_bytes',
    'truncated_count',
    'written',
];

$single_counter = [
    'dropped',
    'processed',
    'queued',
]

$tags['rrd_def'] = $gauge_rrd_def;
foreach ($gauge as $stat) {
    if (! is_null($data['global'][$stat]['max'])) {
        $tags['rrd_name'] = ['app', $name, $app->app_id, 'global_-_'.$stat];
        $stats_fields = [
            'max' => $data['global'][$stat]['max'],
            'mean' => $data['global'][$stat]['mean'],
            'median' => $data['global'][$stat]['median'],
            'min' => $data['global'][$stat]['min'],
            'mode' => $data['global'][$stat]['mode'],
            'sum' => $data['global'][$stat]['sum'],
        ];
        app('Datastore')->put($device, 'app', $tags, $stats_fields);
        $app_data['global'][$stat] = true;
    } else {
        $app_data['global'][$stat] = false;
    }
}

$tags['rrd_def'] = $counter_rrd_def;
foreach ($counter as $stat) {
    if (! is_null($data['global'][$stat]['max'])) {
        $tags['rrd_name'] = ['app', $name, $app->app_id, 'global_-_'.$stat];
        $stats_fields = [
            'max' => $data['global'][$stat]['max'],
            'mean' => $data['global'][$stat]['mean'],
            'median' => $data['global'][$stat]['median'],
            'min' => $data['global'][$stat]['min'],
            'mode' => $data['global'][$stat]['mode'],
            'sum' => $data['global'][$stat]['sum'],
        ];
        app('Datastore')->put($device, 'app', $tags, $stats_fields);
        $app_data['global'][$stat] = true;
    } else {
        $app_data['global'][$stat] = false;
    }
}

foreach ($counter as $stat) {
    if (! is_null($data['global'][$stat]['max'])) {
        $tags['rrd_name'] = ['app', $name, $app->app_id, 'global_-_'.$stat];
        $stats_fields = [
            'max' => $data['global'][$stat]['max'],
            'mean' => $data['global'][$stat]['mean'],
            'median' => $data['global'][$stat]['median'],
            'min' => $data['global'][$stat]['min'],
            'mode' => $data['global'][$stat]['mode'],
            'sum' => $data['global'][$stat]['sum'],
        ];
        app('Datastore')->put($device, 'app', $tags, $stats_fields);
        $app_data['global'][$stat] = true;
    } else {
        $app_data['global'][$stat] = false;
    }
}

foreach ($data['sources'] as $source_name => $source) {
    $app_data['sources'][$source_name] = [];

    $tags['rrd_def'] = $gauge_rrd_def;
    foreach ($gauge as $stat) {
        if (! is_null($source[$stat]['max'])) {
            $tags['rrd_name'] = ['app', $name, $app->app_id, 'source_-_'.$stat.'_-_'.$source_name];
            $stats_fields = [
                'max' => $source[$stat]['max'],
                'mean' => $source[$stat]['mean'],
                'median' => $source[$stat]['median'],
                'min' => $source[$stat]['min'],
                'mode' => $source[$stat]['mode'],
                'sum' => $source[$stat]['sum'],
            ];
            app('Datastore')->put($device, 'app', $tags, $stats_fields);
            $app_data['sources'][$source_name][$stat] = true;
        } else {
            $app_data['sources'][$source_name][$stat] = false;
        }
    }

    $tags['rrd_def'] = $counter_rrd_def;
    foreach ($counter as $stat) {
        if (! is_null($source[$stat]['max'])) {
            $tags['rrd_name'] = ['app', $name, $app->app_id, 'source_-_'.$stat.'_-_'.$source_name];
            $stats_fields = [
                'max' => $source[$stat]['max'],
                'mean' => $source[$stat]['mean'],
                'median' => $source[$stat]['median'],
                'min' => $source[$stat]['min'],
                'mode' => $source[$stat]['mode'],
                'sum' => $source[$stat]['sum'],
            ];
            app('Datastore')->put($device, 'app', $tags, $stats_fields);
            $app_data['sources'][$source_name][$stat] = true;
        } else {
            $app_data['sources'][$source_name][$stat] = false;
        }
    }

    $tags['rrd_def'] = $single_counter_rrd_def;
    foreach ($single_counter as $stat) {
        if (! is_null($source[$stat])) {
            $tags['rrd_name'] = ['app', $name, $app->app_id, 'source_-_'.$stat.'_-_'.$source_name];
            $stats_fields = [
                'data' => $source[$stat],
            ];
            app('Datastore')->put($device, 'app', $tags, $stats_fields);
            $app_data['sources'][$source_name][$stat] = true;
        } else {
            $app_data['sources'][$source_name][$stat] = false;
        }
    }
}

$app->data = $app_data;
update_application($app, 'OK', $metrics);
