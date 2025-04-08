<?php

use App\Models\Eventlog;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'poudriere';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$stat_vars = [
    'BUILT',
    'FAIL',
    'FETCH',
    'IGNORE',
    'QUEUE',
    'REMAIN',
    'SKIP',
    'TIME',
    'build',
    'build-depends',
    'check-sanity',
    'checksum',
    'configure',
    'copy-on-write-faults',
    'cpu-time',
    'data-size',
    'elapsed-times',
    'extract',
    'extract-depends',
    'fetch',
    'fetch-depends',
    'involuntary-context-switches',
    'job-control-count',
    'lib-depends',
    'log_size_done',
    'log_size_latest',
    'log_size_per_package',
    'major-faults',
    'minor-faults',
    'package',
    'package_size_all',
    'package_size_building',
    'package_size_latest',
    'patch',
    'patch-depends',
    'percent-cpu',
    'percent-memory',
    'pkg-depends',
    'read-blocks',
    'received-messages',
    'rss',
    'run-depends',
    'sent-messages',
    'stack-size',
    'stage',
    'swaps',
    'system-time',
    'text-size',
    'threads',
    'user-time',
    'voluntary-context-switches',
    'written-blocks',
];

$metrics = [];
$old_data = $app->data;
$new_data = [
    'status' => $returned['data']['status'],
    'build_info' => $returned['data']['build_info'],
    'history' => $returned['data']['history'],
];

$data = $returned['data'];

$gauge_rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE', 0);

// process total stats, .data.stats
foreach ($stat_vars as $key => $stat) {
    $var_name = 'totals_' . $stat;
    $value = $data['stats'][$stat];
    $rrd_name = ['app', $name, $app->app_id, $var_name];
    $fields = ['data' => $value];
    $metrics[$var_name] = $value;
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $gauge_rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

// process each jail/ports/sets item
$sets = [];
foreach ($data['jailANDportsANDset'] as $jps_key => $jps) {
    $sets[] = $jps_key;
    foreach ($stat_vars as $key => $stat) {
        $var_name = 'jps___' . $jps_key . '___' . $stat;
        $value = $jps[$stat];
        $rrd_name = ['app', $name, $app->app_id, $var_name];
        $fields = ['data' => $value];
        $metrics[$var_name] = $value;
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $gauge_rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);
    }
}

// check for added or removed jps sets
sort($sets);
$old_sets = $old_data['sets'] ?? [];
$added_sets = array_diff($sets, $old_sets);
$removed_sets = array_diff($old_sets, $sets);
$new_data['sets'] = $sets;

$app->data = $new_data;

// if we have any source instances, save and log
if (count($added_sets) > 0 || count($removed_sets) > 0) {
    $log_message = 'Poudriere jail/ports/sets Change:';
    $log_message .= count($added_sets) > 0 ? ' Added ' . implode(',', $added_sets) : '';
    $log_message .= count($removed_sets) > 0 ? ' Removed ' . implode(',', $added_sets) : '';
    Eventlog::log($log_message, $device['device_id'], 'application');
}

// all done so update the app metrics
update_application($app, 'OK', $metrics);
