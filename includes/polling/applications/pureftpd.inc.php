<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'pureftpd';
$app_id = $app['app_id'];
$output = 'OK';

try {
    $pureftpd_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $pureftpd_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$dl_connections = 0;
$ul_connections = 0;
$idle_connections = 0;

$dl_bitrate = 0;
$ul_bitrate = 0;

$users_connected = 0;

foreach ($pureftpd_data as $client) {
    $users_connected++;

    $state = 'DL';
    if (array_key_exists($state, $client)) {
        $dl_connections += $client[$state]['connections'];
        $dl_bitrate += $client[$state]['bitrate'];
    }

    $state = 'UL';
    if (array_key_exists($state, $client)) {
        $ul_connections += $client[$state]['connections'];
        $ul_bitrate += $client[$state]['bitrate'];
    }

    $state = 'IDLE';
    if (array_key_exists($state, $client)) {
        $idle_connections += $client[$state]['connections'];
    }
}

$metrics = [];
//PureFTPd - Connections
$dataset = 'connections';
$rrd_name = ['app', $name, $app_id, $dataset];
$rrd_def = RrdDefinition::make()
    ->addDataset('download', 'GAUGE', 0)
    ->addDataset('upload', 'GAUGE', 0)
    ->addDataset('idle', 'GAUGE', 0);
$fields = [
    'download' => $dl_connections,
    'upload' => $ul_connections,
    'idle' => $idle_connections,
];
$metrics[$dataset] = $fields;
$tags = ['name' => $dataset, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//PureFTPd - connected Users
$dataset = 'users';
$rrd_name = ['app', $name, $app_id, $dataset];
$rrd_def = RrdDefinition::make()
    ->addDataset('total', 'GAUGE', 0);
$fields = [
    'total' => $users_connected,
];
$metrics[$dataset] = $fields;
$tags = ['name' => $dataset, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//PureFTPd - Bitrate
$dataset = 'bitrate';
$rrd_name = ['app', $name, $app_id, $dataset];
$rrd_def = RrdDefinition::make()
    ->addDataset('download', 'GAUGE', 0)
    ->addDataset('upload', 'GAUGE', 0);
$fields = [
    'download' => $dl_bitrate,
    'upload' => $ul_bitrate,
];
$metrics[$dataset] = $fields;
$tags = ['name' => $dataset, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

update_application($app, $output, $metrics);
