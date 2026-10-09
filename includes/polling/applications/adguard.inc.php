<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'adguard';
try {
    $adguard = json_app_get($device, $name, 1)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('queries', 'GAUGE', 0)
    ->addDataset('blocked', 'GAUGE', 0)
    ->addDataset('safebrowsing', 'GAUGE', 0)
    ->addDataset('safesearch', 'GAUGE', 0)
    ->addDataset('parental', 'GAUGE', 0)
    ->addDataset('proc_time', 'GAUGE', 0)
    ->addDataset('running', 'GAUGE', 0, 1)
    ->addDataset('protection', 'GAUGE', 0, 1);

$fields = [
    'queries' => $adguard['num_dns_queries'],
    'blocked' => $adguard['num_blocked_filtering'],
    'safebrowsing' => $adguard['num_replaced_safebrowsing'],
    'safesearch' => $adguard['num_replaced_safesearch'],
    'parental' => $adguard['num_replaced_parental'],
    'proc_time' => $adguard['avg_processing_time'],
    'running' => $adguard['running'],
    'protection' => $adguard['protection_enabled'],
];

$metrics = $fields;
if ($adguard['num_dns_queries'] > 0) {
    $metrics['block_pct'] = round($adguard['num_blocked_filtering'] / $adguard['num_dns_queries'] * 100, 2);
}

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$app->data = ['version' => $adguard['version']];

if (! $adguard['running']) {
    update_application($app, 'ERROR: AdGuard Home is not running', $metrics);
} elseif (! $adguard['protection_enabled']) {
    update_application($app, 'OK', $metrics, 'protection is disabled');
} else {
    update_application($app, 'OK', $metrics);
}
