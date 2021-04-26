<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'puppet-agent';
$app_id = $app['app_id'];
$output = 'OK';

try {
    $puppet_agent_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $puppet_agent_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$puppet_changes = $puppet_agent_data['changes'];
$puppet_events = $puppet_agent_data['events'];
$puppet_resources = $puppet_agent_data['resources'];
$puppet_time = $puppet_agent_data['time'];

$metrics = [];

//
// Changes Processing
//
$rrd_name = ['app', $name, $app_id, 'changes'];
$rrd_def = RrdDefinition::make()
    ->addDataset('total', 'GAUGE', 0);

$fields = [
    'total' => $puppet_changes['total'],
];
$metrics['changes'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// Events Processing
//
$rrd_name = ['app', $name, $app_id, 'events'];
$rrd_def = RrdDefinition::make()
    ->addDataset('success', 'GAUGE', 0)
    ->addDataset('failure', 'GAUGE', 0)
    ->addDataset('total', 'GAUGE', 0);

$fields = [
    'success' => $puppet_events['success'],
    'failure' => $puppet_events['failure'],
    'total' => $puppet_events['total'],
];
$metrics['events'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// Resources Processing
//
$rrd_name = ['app', $name, $app_id, 'resources'];
$rrd_def = RrdDefinition::make()
    ->addDataset('changed', 'GAUGE', 0)
    ->addDataset('corrective_change', 'GAUGE', 0)
    ->addDataset('failed', 'GAUGE', 0)
    ->addDataset('failed_to_restart', 'GAUGE', 0)
    ->addDataset('out_of_sync', 'GAUGE', 0)
    ->addDataset('restarted', 'GAUGE', 0)
    ->addDataset('scheduled', 'GAUGE', 0)
    ->addDataset('skipped', 'GAUGE', 0)
    ->addDataset('total', 'GAUGE', 0);

$fields = [
    'changed' => $puppet_resources['changed'],
    'corrective_change' => $puppet_resources['corrective_change'],
    'failed' => $puppet_resources['failed'],
    'failed_to_restart' => $puppet_resources['failed_to_restart'],
    'out_of_sync' => $puppet_resources['out_of_sync'],
    'restarted' => $puppet_resources['restarted'],
    'scheduled' => $puppet_resources['scheduled'],
    'skipped' => $puppet_resources['skipped'],
    'total' => $puppet_resources['total'],
];
$metrics['resources'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// Time Processing
//
$rrd_name = ['app', $name, $app_id, 'time'];
$rrd_def = RrdDefinition::make()
    ->addDataset('catalog_application', 'GAUGE', 0)
    ->addDataset('config_retrieval', 'GAUGE', 0)
    ->addDataset('convert_catalog', 'GAUGE', 0)
    ->addDataset('fact_generation', 'GAUGE', 0)
    ->addDataset('node_retrieval', 'GAUGE', 0)
    ->addDataset('plugin_sync', 'GAUGE', 0)
    ->addDataset('schedule', 'GAUGE', 0)
    ->addDataset('transaction_evaluation', 'GAUGE', 0)
    ->addDataset('total', 'GAUGE', 0);

$fields = [
    'catalog_application' => $puppet_time['catalog_application'],
    'config_retrieval' => $puppet_time['config_retrieval'],
    'convert_catalog' => $puppet_time['convert_catalog'],
    'fact_generation' => $puppet_time['fact_generation'],
    'node_retrieval' => $puppet_time['node_retrieval'],
    'plugin_sync' => $puppet_time['plugin_sync'],
    'schedule' => $puppet_time['schedule'],
    'transaction_evaluation' => $puppet_time['transaction_evaluation'],
    'total' => $puppet_time['total'],
];
$metrics['time'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// Last Rung Processing
//
$rrd_name = ['app', $name, $app_id, 'last_run'];
$rrd_def = RrdDefinition::make()
    ->addDataset('last_run', 'GAUGE', 0);

$fields = [
    'last_run' => round(intval($puppet_time['last_run']) / 60, 0), // diff seconds to minutes
];
$metrics['last_run'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

update_application($app, $output, $metrics);
