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
    ->addDataset('1d_size_diff', 'GAUGE')
    ->addDataset('1d_size_diffp', 'GAUGE')
    ->addDataset('2d_size_diff', 'GAUGE')
    ->addDataset('2d_size_diffp', 'GAUGE')
    ->addDataset('3d_size_diff', 'GAUGE')
    ->addDataset('3d_size_diffp', 'GAUGE')
    ->addDataset('4d_size_diff', 'GAUGE')
    ->addDataset('4d_size_diffp', 'GAUGE')
    ->addDataset('5d_size_diff', 'GAUGE')
    ->addDataset('5d_size_diffp', 'GAUGE')
    ->addDataset('6d_size_diff', 'GAUGE')
    ->addDataset('6d_size_diffp', 'GAUGE')
    ->addDataset('7d_size_diff', 'GAUGE')
    ->addDataset('7d_size_diffp', 'GAUGE')
    ->addDataset('size', 'GAUGE');

$set_rrd_def = RrdDefinition::make()
    ->addDataset('max_size', 'GAUGE')
    ->addDataset('max_size_diff', 'GAUGE')
    ->addDataset('max_size_diffp', 'GAUGE')
    ->addDataset('min_size', 'GAUGE')
    ->addDataset('min_size_diff', 'GAUGE')
    ->addDataset('min_size_diffp', 'GAUGE')
    ->addDataset('size', 'GAUGE');

$app_data=['sets'=>[],'no_minus_d'=>$data['no_minus_d']];

$rrd_name = ['app', $name, $app->app_id];
$fields=[
    'max_size' => $data['max_size'],
    'max_size_diff' => $data['max_size_diff'],
    'max_size_diffp' => $data['max_size_diffp'],
    'min_size' => $data['min_size'],
    'min_size_diff' => $data['min_size_diff'],
    'min_size_diffp' => $data['min_size_diffp'],
    'size' => $data['size'],
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $set_rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

foreach ($data['sets'] as $set_name => $set_data ) {
    $app_data['sets'][$set_name]=[
        'files' => array_keys($set_data['files']),
        'max_size' => $set_data['max_size'],
        'max_size_diff' => $set_data['max_size_diff'],
        'max_size_diffp' => $set_data['max_size_diffp'],
        'min_size' => $set_data['min_size'],
        'min_size_diff' => $set_data['min_size_diff'],
        'min_size_diffp' => $set_data['min_size_diffp'],
        'size' => $set_data['size'],
    ];

    $rrd_name = ['app', $name, $app->app_id, $set_name];
    $fields=[
        'max_size' => $set_data['max_size'],
        'max_size_diff' => $set_data['max_size_diff'],
        'max_size_diffp' => $set_data['max_size_diffp'],
        'min_size' => $set_data['min_size'],
        'min_size_diff' => $set_data['min_size_diff'],
        'min_size_diffp' => $set_data['min_size_diffp'],
        'size' => $set_data['size'],
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $set_rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    foreach ($set_data['files'] as $log_name => $log_data ) {
        $rrd_name = ['app', $name, $app->app_id, $set_name.'_____-_____'.$log_name];
        $fields=[
            '1d_size_diff' => $log_data['1d_size_diff'],
            '1d_size_diffp' => $log_data['1d_size_diffp'],
            '2d_size_diff' => $log_data['1d_size_diff'],
            '2d_size_diffp' => $log_data['1d_size_diffp'],
            '3d_size_diff' => $log_data['1d_size_diff'],
            '3d_size_diffp' => $log_data['1d_size_diffp'],
            '4d_size_diff' => $log_data['1d_size_diff'],
            '4d_size_diffp' => $log_data['1d_size_diffp'],
            '5d_size_diff' => $log_data['1d_size_diff'],
            '5d_size_diffp' => $log_data['1d_size_diffp'],
            '6d_size_diff' => $log_data['1d_size_diff'],
            '6d_size_diffp' => $log_data['1d_size_diffp'],
            '7d_size_diff' => $log_data['1d_size_diff'],
            '7d_size_diffp' => $log_data['1d_size_diffp'],
            'size' => $log_data['size'],
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);
    }
}

$app->data=$app_data;
update_application($app, 'OK', $fields);
