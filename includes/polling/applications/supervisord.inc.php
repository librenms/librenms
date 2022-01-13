<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'supervisord';
$app_id = $app['app_id'];
$output = 'OK';

try {
    $supervisord_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, 'ERROR', []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app_id];

$metrics = [];
$rrd_def = RrdDefinition::make();
foreach ($supervisord_data['total'] as $status => $value) {
    $rrd_def->addDataset($status, 'GAUGE', 0);
}

$fields = $supervisord_data['total'];

$metrics['total'] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$rrd_def = RrdDefinition::make()
    ->addDataset('state', 'GAUGE', 0)
    ->addDataset('uptime', 'GAUGE', 0);

foreach ($supervisord_data['processes'] as $data) {
    $process = $data['name'];

    $rrd_name = ['app', $name, $app_id, $process];

    $fields = [
        'state' => $data['state'],
        'uptime' => $data['uptime'],
    ];

    $metrics['process_' . $process] = $fields;
    $tags = ['name' => $process, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

update_application($app, $output, $metrics);
