<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'nfs';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;

    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

include 'includes/nfs-shared.inc.php';

$data = $returned['data'];

$rrd_def = RrdDefinition::make()
    ->addDataset('data', 'COUNTER');

$gauge_rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE');

$metrics = [];

foreach (array_keys($nfs_stat_keys) as $stat_name) {
    $rrd_name = ['app', $name, $app->app_id, $stat_name];
    $fields = ['data' => $returned['data']['stats'][$stat_name]];

    if (isset($gauge_stats[$stat_name])) {
        $rrd_def_to_use = $gauge_rrd_def;
    } else {
        $rrd_def_to_use = $rrd_def;
    }

    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_to_use, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $metrics[$stat_name] = $returned['data']['stats'][$stat_name];
}

$app_data = [
    'is_client' => $returned['data']['is_client'],
    'is_server' => $returned['data']['is_server'],
    'mounts' => $returned['data']['mounts'],
    'mounted_by' => $returned['data']['mounted_by'],
    'os' => $returned['data']['os'],
];

$app->data = $app_data;
update_application($app, 'OK', $metrics);
