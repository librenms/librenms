<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'linux_softnet_stat';

try {
    $data = json_app_get($device, $name)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message
    return;
}

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('cpu_collision', 'DERIVE')
    ->addDataset('flow_limit_count', 'DERIVE')
    ->addDataset('packet_drop', 'DERIVE')
    ->addDataset('packet_process', 'DERIVE')
    ->addDataset('received_rps', 'DERIVE')
    ->addDataset('softnet_backlog_len', 'DERIVE')
    ->addDataset('time_squeeze', 'DERIVE');

$fields = [
    'cpu_collision' => $data['totals']['cpu_collision'],
    'flow_limit_count' => $data['totals']['flow_limit_count'],
    'packet_drop' => $data['totals']['packet_drop'],
    'packet_process' => $data['totals']['packet_process'],
    'received_rps' => $data['totals']['received_rps'],
    'softnet_backlog_len' => $data['totals']['softnet_backlog_len'],
    'time_squeeze' => $data['totals']['time_squeeze'],
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
