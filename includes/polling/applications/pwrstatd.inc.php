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

$rrd_name = ['app', $name, $app->app_id];
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
    $sn = is_string($data['sn']) ? filter_var($data['sn'], FILTER_SANITIZE_STRING) : null;

    if (is_null($data['sn'])) {
        echo PHP_EOL . $name . ':' . ' Invalid or no psu serial number found.' . PHP_EOL;

        continue;
    }

    $mruntime = is_int($data['mruntime']) ? $data['mruntime'] : null;
    $pcapacity = is_int($data['pcapacity']) ? $data['pcapacity'] : null;
    $pload = is_int($data['pload']) ? $data['pload'] : null;
    $voutput = is_int($data['voutput']) ? $data['voutput'] : null;
    $vrating = is_int($data['vrating']) ? $data['vrating'] : null;
    $vutility = is_int($data['vutility']) ? $data['vutility'] : null;
    $wload = is_int($data['wload']) ? $data['wload'] : null;
    $wrating = is_int($data['wrating']) ? $data['wrating'] : null;

    $rrd_name = ['app', $name, $app->app_id, $sn];

    $fields = [
        'mruntime'  => $mruntime,
        'pcapacity' => $pcapacity,
        'pload'     => $pload,
        'voutput'   => $voutput,
        'vrating'   => $vrating,
        'vutility'  => $vutility,
        'wload'     => $wload,
        'wrating'   => $wrating,
    ];

    $metrics[$sn] = $fields;
    $tags = ['name' => $sn, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

update_application($app, $output, $metrics);
