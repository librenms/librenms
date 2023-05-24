<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'logsize';

try {
    $returned = json_app_get($device, $name ,1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;

    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message
    return;
}

$data=$returned['data'];

$rrd_def = RrdDefinition::make()
    ->addDataset('size', 'GAUGE');

$set_rrd_def = RrdDefinition::make()
    ->addDataset('max_size', 'GAUGE')
    ->addDataset('mean_size', 'GAUGE')
    ->addDataset('median_size', 'GAUGE')
    ->addDataset('mode_size', 'GAUGE')
    ->addDataset('min_size', 'GAUGE')
    ->addDataset('size', 'GAUGE');

$app_data=['sets'=>[],'no_minus_d'=>$data['no_minus_d']];

$rrd_name = ['app', $name, $app->app_id];
$fields=[
    'max_size' => $data['max_size'],
    'mean_size' => $data['mean_size'],
    'median_size' => $data['median_size'],
    'mode_size' => $data['mode_size'],
    'min_size' => $data['min_size'],
    'size' => $data['size'],
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $set_rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

foreach ($data['sets'] as $set_name => $set_data ) {
    $app_data['sets'][$set_name]=[
        'files' => array_keys($set_data['files']),
        'max_size' => $set_data['max_size'],
        'mean_size' => $set_data['mean_size'],
        'median_size' => $set_data['median_size'],
        'mode_size' => $set_data['mode_size'],
        'min_size' => $set_data['min_size'],
        'size' => $set_data['size'],
        'log_sizes' => [],
    ];

    $rrd_name = ['app', $name, $app->app_id, $set_name];
    $fields=[
        'max_size' => $set_data['max_size'],
        'mean_size' => $set_data['mean_size'],
        'median_size' => $set_data['median_size'],
        'mode_size' => $set_data['mode_size'],
        'min_size' => $set_data['min_size'],
        'size' => $set_data['size'],
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $set_rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    foreach ($set_data['files'] as $log_name => $log_data ) {
        $rrd_name = ['app', $name, $app->app_id, $set_name.'_____-_____'.$log_name];
        $fields=[
            'size' => $log_data['size'],
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);

        $app_data['sets'][$set_name]['log_sizes'][$log_name]=$log_data['size'];
    }

    uasort($app_data['sets'][$set_name]['log_sizes'], function ($a,$b){
        if ($a == $b) {
            return 0;
        }
        return ($a > $b) ? -1 : 1;
    });
}

$app->data=$app_data;
update_application($app, 'OK', $fields);
