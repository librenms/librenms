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

$rrd_def = RrdDefinition::make()
    ->addDataset('backlog_length', 'GAUGE')
    ->addDataset('cpu_collision', 'COUNTER')
    ->addDataset('flow_limit', 'COUNTER')
    ->addDataset('packet_dropped', 'COUNTER')
    ->addDataset('packets', 'COUNTER')
    ->addDataset('received_rps', 'COUNTER')
    ->addDataset('time_squeeze', 'COUNTER')
    ->addDataset('budget', 'GAUGE')
    ->addDataset('budget_usecs', 'GAUGE');

$fields = [
    'backlog_length' => $data['totals']['backlog_length'],
    'cpu_collision' => $data['totals']['cpu_collision'],
    'flow_limit' => $data['totals']['flow_limit'],
    'packet_dropped' => $data['totals']['packet_dropped'],
    'packets' => $data['totals']['packets'],
    'received_rps' => $data['totals']['received_rps'],
    'time_squeeze' => $data['totals']['time_squeeze'],
    'budget' => $data['budget'],
    'budget_usecs' => $data['budget_usecs'],
];

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id],
    'rrd_def' => $rrd_def,
];
data_update($device, 'app', $tags, $fields);
$app->data = ['budget' => $data['budget'], 'budget_usecs' => $data['budget_usecs']];
update_application($app, 'OK', $fields);
