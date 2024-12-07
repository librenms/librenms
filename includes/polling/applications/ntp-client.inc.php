<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'ntp-client';

try {
    $ntp = json_app_get($device, $name);
} catch (JsonAppParsingFailedException $e) {
    // Legacy script, build compatible array
    $legacy = $e->getOutput();

    $ntp = [
        'data' => [],
    ];
    [$ntp['data']['offset'], $ntp['data']['frequency'], $ntp['data']['sys_jitter'],
        $ntp['data']['clk_jitter'], $ntp['data']['clk_wander']] = explode("\n", $legacy);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_def = RrdDefinition::make()
    ->addDataset('offset', 'GAUGE', -1000, 1000)
    ->addDataset('frequency', 'GAUGE', -1000, 1000)
    ->addDataset('jitter', 'GAUGE', -1000, 1000)
    ->addDataset('noise', 'GAUGE', -1000, 1000)
    ->addDataset('stability', 'GAUGE', -1000, 1000);

$fields = [
    'offset' => $ntp['data']['offset'],
    'frequency' => $ntp['data']['frequency'],
    'jitter' => $ntp['data']['sys_jitter'],
    'noise' => $ntp['data']['clk_jitter'],
    'stability' => $ntp['data']['clk_wander'],
];

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id],
    'rrd_def' => $rrd_def,
];
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
