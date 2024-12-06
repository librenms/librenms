<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$app_name = 'linux_config_files';
$output_success = 'OK';
$polling_type = 'app';

try {
    $polling_data = json_app_get($device, $app_name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $polling_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $app_name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

    return;
}

$rrd_name = [$polling_type, $app_name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('number_of_confs', 'GAUGE', 0);

$number_of_confs = (int) $polling_data['number_of_confs'] ?? null;

$fields = [
    'number_of_confs' => $number_of_confs,
];

$tags = [
    'app_id' => $app->app_id,
    'name' => $app_name,
    'rrd_def' => $rrd_def,
    'rrd_name' => $rrd_name,
];

data_update($device, $polling_type, $tags, $fields);

update_application($app, $output_success, $fields);
