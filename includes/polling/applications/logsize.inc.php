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

$rrd_name = ['app', $name, $app->app_id];
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


$app_data=['sets'=>[]];

foreach ($data['sets'] as $set_name => $set_data ) {
    $app_data['sets'][$set_name]=[
        'chomp' => $set_data['chomp'],
        'dir' => $set_data['dir'],
        'files' => array_keys($set_data['files']),
        'log_end' => $set_data['log_end'],
    ];
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
