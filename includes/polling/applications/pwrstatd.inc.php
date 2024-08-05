<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'pwrstatd';
$output = 'OK';

try {
    $pwrstatd_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $pwrstatd_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_def = RrdDefinition::make()
    ->addDataset('mruntime', 'GAUGE', 0)
    ->addDataset('pcapacity', 'GAUGE', 0, 100)
    ->addDataset('pload', 'GAUGE', 0, 100)
    ->addDataset('voutput', 'GAUGE', 0)
    ->addDataset('vrating', 'GAUGE', 0)
    ->addDataset('vutility', 'GAUGE', 0)
    ->addDataset('wload', 'GAUGE', 0)
    ->addDataset('wrating', 'GAUGE', 0);

$metrics = [];
foreach ($pwrstatd_data as $data) {
    if (! is_string($data['sn'])) {
        echo PHP_EOL . $name . ':' . ' Invalid or no psu serial number found.' . PHP_EOL;

        continue;
    }

    $fields = [
        'mruntime' => $data['mruntime'],
        'pcapacity' => $data['pcapacity'],
        'pload' => $data['pload'],
        'voutput' => $data['voutput'],
        'vrating' => $data['vrating'],
        'vutility' => $data['vutility'],
        'wload' => $data['wload'],
        'wrating' => $data['wrating'],
    ];

    $sn = \LibreNMS\Util\Clean::fileName($data['sn']);
    $rrd_name = ['app', $name, $app->app_id, $sn];
    $metrics[$sn] = $fields;
    $tags = ['name' => $sn, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

update_application($app, $output, $metrics);
