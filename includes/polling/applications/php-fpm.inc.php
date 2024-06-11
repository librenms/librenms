<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'php-fpm';

try {
    // phpfpmsp is the original name and being kept for compatbility purposes... sp stood for single pool
    $extend_return = json_app_get($device, 'phpfpmsp');
} catch (JsonAppParsingFailedException $e) {
    // Legacy script, build compatible array
    $phpfpm = $e->getOutput();

    [$pool,$start_time,$start_since,$accepted_conn,$listen_queue,$max_listen_queue,$listen_queue_len,$idle_processes,
     $active_processes,$total_processes,$max_active_processes,$max_children_reached,$slow_requests] = explode("\n", $phpfpm);

    $rrd_name = ['app', $name, $app->app_id];
    $rrd_def = RrdDefinition::make()
        ->addDataset('lq', 'GAUGE', 0)
        ->addDataset('mlq', 'GAUGE', 0)
        ->addDataset('ip', 'GAUGE', 0)
        ->addDataset('ap', 'GAUGE', 0)
        ->addDataset('tp', 'GAUGE', 0)
        ->addDataset('map', 'GAUGE', 0)
        ->addDataset('mcr', 'GAUGE', 0)
        ->addDataset('sr', 'GAUGE', 0);

    $fields = [
        'lq' => $listen_queue,
        'mlq' => $max_listen_queue,
        'ip' => $idle_processes,
        'ap' => $active_processes,
        'tp' => $total_processes,
        'map' => $max_active_processes,
        'mcr' => $max_children_reached,
        'sr' => $slow_requests,
    ];

    $app->data = ['version' => 'legacy'];

    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
    update_application($app, $phpfpm, $fields);

    return;
}

$var_mappings = [
    'accepted_conn' => 'accepted conn',
    'active_processes' => 'active processes',
    'idle_processes' => 'idle processes',
    'listen_queue' => 'listen queue',
    'listen_queue_len' => 'listen queue len',
    'max_active_processes' => 'max active processes',
    'max_children_reached' => 'max children reached',
    'max_listen_queue' => 'max listen queue',
    'slow_requests' => 'slow requests',
    'start_since' => 'start since',
];

$new_app_data = [
    'version' => $extend_return['version'],
    'pools' => [],
];

$metrics = [
    'errored' => $extend_return['data']['errored'],
];

$rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE', 0);

// process totals
foreach ($var_mappings as $stat => $stat_key) {
    if (isset($extend_return['data']['totals'][$stat_key])) {
        $rrd_name = ['app', $name, $app->app_id, 'totals___' . $stat];
        $fields = ['data' => $extend_return['data']['totals'][$stat_key]];

        $metrics['totals_' . $stat] = $value;

        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);
    }
}
