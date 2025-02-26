<?php

use App\Models\Eventlog;
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

    update_application($app, 'OK', $fields);

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
    'total_processes' => 'total processes',
    'last_request_cpu' => 'last request cpu',
];

$new_app_data = [
    'version' => $extend_return['version'],
    'pools' => [],
];

$metrics = [
    'errored' => $extend_return['data']['errored'],
];

$old_app_data = $app->data;

$counter_rrd_def = RrdDefinition::make()
    ->addDataset('data', 'DERIVE', 0);

$gauge_rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE', 0);

// process pools
foreach ($extend_return['data']['pools'] as $pool => $pool_stats) {
    $new_app_data['pools'][] = $pool;
    foreach ($var_mappings as $stat => $stat_key) {
        $rrd_name = ['app', $name, $app->app_id, 'pools___' . $pool . '___' . $stat];
        $fields = ['data' => $extend_return['data']['pools'][$pool][$stat_key]];

        $metrics['pools___' . $pool . '___' . $stat] = $extend_return['data']['pools'][$pool][$stat_key];

        if ($stat == 'accepted_conn' || $stat == 'slow_requests' || $stat == 'max_children_reached') {
            $rrd_def = $counter_rrd_def;
        } else {
            $rrd_def = $gauge_rrd_def;
        }

        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);
    }
}

// process totals
foreach ($var_mappings as $stat => $stat_key) {
    $rrd_name = ['app', $name, $app->app_id, 'totals___' . $stat];
    $fields = ['data' => $extend_return['data']['totals'][$stat_key]];

    $metrics['totals_' . $stat] = $extend_return['data']['totals'][$stat_key];

    if ($stat == 'accepted_conn' || $stat == 'slow_requests' || $stat == 'max_children_reached') {
        $rrd_def = $counter_rrd_def;
    } else {
        $rrd_def = $gauge_rrd_def;
    }

    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

// check for added or removed pools
$old_pools = $old_app_data['pools'] ?? [];
$new_pools = $new_app_data['pools'] ?? [];
$added_pools = array_diff($new_pools, $old_pools);
$removed_pools = array_diff($old_pools, $new_pools);

// if we have any changes in pools, log it
if (count($added_pools) > 0 || count($removed_pools) > 0) {
    $log_message = 'PHP-FPM Pool Change:';
    $log_message .= count($added_pools) > 0 ? ' Added ' . implode(',', $added_pools) : '';
    $log_message .= count($removed_pools) > 0 ? ' Removed ' . implode(',', $added_pools) : '';
    Eventlog::log($log_message, $device['device_id'], 'application');
}

$app->data = $new_app_data;

update_application($app, 'OK', $metrics);
