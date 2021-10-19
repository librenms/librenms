<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'docker';
$app_id = $app['app_id'];
$output = 'OK';

function convertToBytes(string $from): ?int
{
    $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
    $number = substr($from, 0, -3);
    $suffix = substr($from, -3);

    //B or no suffix
    if (is_numeric(substr($suffix, 0, 1))) {
        return (int) $from;
    }

    $exponent = array_flip($units)[$suffix] ?? null;
    if ($exponent === null) {
        return null;
    }

    return (int) ($number * (1024 ** $exponent));
}

try {
    $docker_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $docker_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('cpu_usage', 'GAUGE', 0, 100)
    ->addDataset('pids', 'GAUGE', 0)
    ->addDataset('mem_perc', 'GAUGE', 0, 100)
    ->addDataset('mem_used', 'GAUGE', 0)
    ->addDataset('mem_limit', 'GAUGE', 0);

$metrics = [];
foreach ($docker_data as $data) {
    $container = $data['container'];

    $rrd_name = ['app', $name, $app_id, $container];

    $fields = [
        'cpu_usage' => (float) $data['cpu'],
        'pids' => $data['pids'],
        'mem_limit' => convertToBytes($data['memory']['limit']),
        'mem_used' => convertToBytes($data['memory']['used']),
        'mem_perc' => (float) $data['memory']['perc'],
    ];

    $metrics[$container] = $fields;
    $tags = ['name' => $container, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

update_application($app, $output, $metrics);
