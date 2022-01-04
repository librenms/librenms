<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'mdadm';
$app_id = $app['app_id'];
$output = 'OK';

try {
    $mdadm_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $mdadm_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('level', 'GAUGE', 0)
    ->addDataset('size', 'GAUGE', 0)
    ->addDataset('disc_count', 'GAUGE', 0)
    ->addDataset('hotspare_count', 'GAUGE', 0)
    ->addDataset('degraded', 'GAUGE', 0)
    ->addDataset('sync_speed', 'GAUGE', 0)
    ->addDataset('sync_completed', 'GAUGE', 0);

$metrics = [];
foreach ($mdadm_data as $data) {
    $array_name = $data['name'];
    $level = $data['level'];
    $size = $data['size'];
    $device_list = $data['device_list'];
    $missing_device_list = $data['missing_device_list'];
    $disc_count = $data['disc_count'];
    $hotspare_count = $data['hotspare_count'];
    $degraded = $data['degraded'];
    $sync_speed = $data['sync_speed'];
    $sync_completed = $data['sync_completed'];

    $rrd_name = ['app', $name, $app_id, $array_name];

    $array_level = str_replace('raid', '', $level);

    $fields = [
        'level'          => $array_level,
        'size'           => $size,
        'disc_count'     => $disc_count,
        'hotspare_count' => $hotspare_count,
        'degraded'       => $degraded,
        'sync_speed'     => $sync_speed,
        'sync_completed' => $sync_completed,
    ];

    $metrics[$array_name] = $fields;
    $tags = ['name' => $array_name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}
update_application($app, $output, $metrics);
