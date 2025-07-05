<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'logsize';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;

    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$data = $returned['data'];

$rrd_def = RrdDefinition::make()
    ->addDataset('size', 'GAUGE');

$set_rrd_def = RrdDefinition::make()
    ->addDataset('max_size', 'GAUGE')
    ->addDataset('mean_size', 'GAUGE')
    ->addDataset('median_size', 'GAUGE')
    ->addDataset('mode_size', 'GAUGE')
    ->addDataset('min_size', 'GAUGE')
    ->addDataset('size', 'GAUGE');

$app_data = [
    'sets' => [],
    'no_minus_d' => $data['no_minus_d'] ?? false,
];

$rrd_name = ['app', $name, $app->app_id];
$fields = [
    'max_size' => $data['max'],
    'mean_size' => $data['mean'],
    'median_size' => $data['median'],
    'mode_size' => $data['mode'],
    'min_size' => $data['min'],
    'size' => $data['size'],
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $set_rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$metrics = $fields;

foreach ($data['sets'] as $set_name => $set_data) {
    if (isset($set_data['files']) && is_array($set_data['files'])) {
        $app_data['sets'][$set_name] = [
            'files' => array_keys($set_data['files']) ?? null,
            'max_size' => $set_data['max'] ?? null,
            'mean_size' => $set_data['mean'] ?? null,
            'median_size' => $set_data['median'] ?? null,
            'mode_size' => $set_data['mode'] ?? null,
            'min_size' => $set_data['min'] ?? null,
            'size' => $set_data['size'] ?? null,
            'log_sizes' => [],
        ];

        $metrics['set_' . $set_name . '_max_size'] = $set_data['max'] ?? null;
        $metrics['set_' . $set_name . '_mean_size'] = $set_data['mean'] ?? null;
        $metrics['set_' . $set_name . '_median_size'] = $set_data['median'] ?? null;
        $metrics['set_' . $set_name . '_mode_size'] = $set_data['mode'] ?? null;
        $metrics['set_' . $set_name . '_min_size'] = $set_data['min'] ?? null;
        $metrics['set_' . $set_name . '_size'] = $set_data['size'] ?? null;

        $rrd_name = ['app', $name, $app->app_id, $set_name];
        $fields = [
            'max_size' => $set_data['max'] ?? null,
            'mean_size' => $set_data['mean'] ?? null,
            'median_size' => $set_data['median'] ?? null,
            'mode_size' => $set_data['mode'] ?? null,
            'min_size' => $set_data['min'] ?? null,
            'size' => $set_data['size'] ?? null,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $set_rrd_def, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        foreach ($set_data['files'] as $log_name => $log_size) {
            $rrd_name = ['app', $name, $app->app_id, $set_name . '_____-_____' . $log_name];
            $fields = [
                'size' => $log_size,
            ];
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
            app('Datastore')->put($device, 'app', $tags, $fields);

            $app_data['sets'][$set_name]['log_sizes'][$log_name] = $log_size;

            //$metrics['set_' . $set_name . '_files_' .$log_name] = $log_size;
        }

        foreach ($set_data['unseen'] as $log_name) {
            $rrd_name = ['app', $name, $app->app_id, $set_name . '_____-_____' . $log_name];
            $fields = [
                'size' => 0,
            ];
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
            app('Datastore')->put($device, 'app', $tags, $fields);

            $app_data['sets'][$set_name]['log_sizes'][$log_name] = 0;
            $app_data['sets'][$set_name]['files'][] = $log_name;

            //$metrics['set_' . $set_name . '_files_' .$log_name] = 0;
        }

        uasort($app_data['sets'][$set_name]['log_sizes'], function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        });
    }
}

$app->data = $app_data;
update_application($app, 'OK', $metrics);
