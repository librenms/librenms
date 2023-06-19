<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'mojo_cape_submit';

try {
    $data = json_app_get($device, $name)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

if (! is_array($app->data['slugs'])) {
    $app->data['slugs'] = [];
}

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('errors', 'app_protos', 0)
    ->addDataset('errors', 'hash_changed', 0)
    ->addDataset('errors', 'size_max', 0)
    ->addDataset('errors', 'size_mean', 0)
    ->addDataset('errors', 'size_median', 0)
    ->addDataset('errors', 'size_min', 0)
    ->addDataset('errors', 'size_mode', 0)
    ->addDataset('errors', 'size_stddev', 0)
    ->addDataset('errors', 'size_sum', 0)
    ->addDataset('errors', 'sub_count', 0);

$fields = [
    'app_protos' => $data['totals']['app_protos'];
    'hash_changed' => $data['totals']['hash_changed'];
    'size_max' => $data['totals']['size_max'];
    'size_mean' => $data['totals']['size_mean'];
    'size_median' => $data['totals']['size_median'];
    'size_min' => $data['totals']['size_min'];
    'size_mode' => $data['totals']['size_mode'];
    'size_stddev' => $data['totals']['size_stddev'];
    'size_sum' => $data['totals']['size_sum'];
    'sub_count' => $data['totals']['sub_count'];
];

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$new_slugs=[];
foreach ($data['slugs'] as $slug => $slug_data) {
    $fields = [
        'app_protos' => $slug_data['app_protos'];
        'hash_changed' => $slug_data['hash_changed'];
        'size_max' => $slug_data['size_max'];
        'size_mean' => $slug_data['size_mean'];
        'size_median' => $slug_data['size_median'];
        'size_min' => $slug_data['size_min'];
        'size_mode' => $slug_data['size_mode'];
        'size_stddev' => $slug_data['size_stddev'];
        'size_sum' => $slug_data['size_sum'];
        'sub_count' => $slug_data['sub_count'];
    ];
    $rrd_name = ['app', $name, $app->app_id, 'slugs___-___' . $slug];
    if (!isset($app->data['slugs'][$slug])) {
        array_push($new_slugs, $slug);
    }
    $app->data['slugs'][$slug]=>1;
}

if ($data['totals']['hash_changed'] >= 1) {
    log_event('Mojo Cape Submit has recieved submissions with changed hashes: ' . json_encode($data['changed_hashes']), $device, 'application', 5);
}

update_application($app, 'OK', $data['totals']);
