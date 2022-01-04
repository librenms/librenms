<?php

// Polls backupninja statistics from script via SNMP
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'backupninja';
$app_id = $app['app_id'];
$output = 'OK';

try {
    $backupninja_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $backupninja_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$metrics = [];

$category = 'overview';
$rrd_name = ['app', $name, $app_id, $category];
$rrd_def = RrdDefinition::make()
    ->addDataset('last_actions', 'GAUGE', 0)
    ->addDataset('last_fatal', 'GAUGE', 0)
    ->addDataset('last_error', 'GAUGE', 0)
    ->addDataset('last_warning', 'GAUGE', 0);

$fields = [
    'last_actions'   => $backupninja_data['last_actions'],
    'last_fatal'     => $backupninja_data['last_fatal'],
    'last_error'     => $backupninja_data['last_error'],
    'last_warning'   => $backupninja_data['last_warning'],
];
$metrics[$category] = $fields;

// Debug
d_echo("backupninja : $fields");

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

update_application($app, $output, $metrics);
