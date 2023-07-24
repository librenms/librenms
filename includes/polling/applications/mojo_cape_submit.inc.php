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

$app_data = $app->data;
if (! is_array($app_data['slugs'])) {
    $app_data['slugs'] = [];
}

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('app_protos', 'GAUGE', 0)
    ->addDataset('hash_changed', 'GAUGE', 0)
    ->addDataset('size_max', 'GAUGE', 0)
    ->addDataset('size_mean', 'GAUGE', 0)
    ->addDataset('size_median', 'GAUGE', 0)
    ->addDataset('size_min', 'GAUGE', 0)
    ->addDataset('size_mode', 'GAUGE', 0)
    ->addDataset('size_stddev', 'GAUGE', 0)
    ->addDataset('size_sum', 'GAUGE', 0)
    ->addDataset('sub_count', 'GAUGE', 0);

$fields = [
    'app_protos' => $data['totals']['app_protos'],
    'hash_changed' => $data['totals']['hash_changed'],
    'size_max' => $data['totals']['size_max'],
    'size_mean' => $data['totals']['size_mean'],
    'size_median' => $data['totals']['size_median'],
    'size_min' => $data['totals']['size_min'],
    'size_mode' => $data['totals']['size_mode'],
    'size_stddev' => $data['totals']['size_stddev'],
    'size_sum' => $data['totals']['size_sum'],
    'sub_count' => $data['totals']['sub_count'],
];

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$new_slugs = [];
$seen_slugs = [];
foreach ($data['slugs'] as $slug => $slug_data) {
    $fields = [
        'app_protos' => $slug_data['app_protos'],
        'hash_changed' => $slug_data['hash_changed'],
        'size_max' => $slug_data['size_max'],
        'size_mean' => $slug_data['size_mean'],
        'size_median' => $slug_data['size_median'],
        'size_min' => $slug_data['size_min'],
        'size_mode' => $slug_data['size_mode'],
        'size_stddev' => $slug_data['size_stddev'],
        'size_sum' => $slug_data['size_sum'],
        'sub_count' => $slug_data['sub_count'],
    ];
    $rrd_name = ['app', $name, $app->app_id, 'slugs___-___' . $slug];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
    if (! isset($app_data['slugs'][$slug])) {
        array_push($new_slugs, $slug);
    }
    $app_data['slugs'][$slug] = $slug_data['sub_count'];
    $seen_slugs[$slug] = 1;
}

// make sure we update the RRDs for slugs that have not been seen
// if this is not done slugs that do not generate data regularly
// will only display nan
foreach ($app_data['slugs'] as $slug => $slug_data) {
    if (! isset($seen_slugs[$slug])) {
        $fields = [
            'app_protos' => 0,
            'hash_changed' => 0,
            'size_max' => 0,
            'size_mean' => 0,
            'size_median' => 0,
            'size_min' => 0,
            'size_mode' => 0,
            'size_stddev' => 0,
            'size_sum' => 0,
            'sub_count' => 0,
        ];
        $rrd_name = ['app', $name, $app->app_id, 'slugs___-___' . $slug];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);
        if (! isset($app_data['slugs'][$slug])) {
            array_push($new_slugs, $slug);
        }
        $app_data['slugs'][$slug] = 0;
    }
}

if ($data['totals']['hash_changed'] >= 1) {
    log_event('Mojo Cape Submit has recieved submissions with changed hashes: ' . json_encode($data['changed_hashes']), $device, 'application', 5);
}

if (isset($new_slugs[0])) {
    log_event('Mojo Cape Submit has seen one or more new slugs: ' . json_encode($new_slugs), $device, 'application', 1);
}

uasort($app_data['slugs'], function ($a, $b) {
    if ($a == $b) {
        return 0;
    }

    return ($a > $b) ? -1 : 1;
});

$app->data = $app_data;
update_application($app, 'OK', $data['totals']);
