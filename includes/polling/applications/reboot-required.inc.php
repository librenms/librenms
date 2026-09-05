<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'reboot-required';

try {
    $reboot_required_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $reboot_required_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$reboot = $reboot_required_data['reboot'] ? 1 : 0;
$status = $reboot ? 'Reboot required' : 'No reboot required';

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()->addDataset('reboot', 'GAUGE', 0, 1);

$fields = ['reboot' => $reboot];

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_def' => $rrd_def,
    'rrd_name' => $rrd_name,
];

app('Datastore')->put($device, 'app', $tags, $fields);
update_application($app, $status, $fields, $status);
