<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'systemd-timesyncd';

try {
    $timesyncd = json_app_get($device, $name);
} catch (JsonAppParsingFailedException $e) {
    // Legacy script, build compatible array
    $legacy = $e->getOutput();

    $timesyncd = [
        'data' => [],
    ];
    [$timesyncd['data']['offset'], $timesyncd['data']['frequency'], $timesyncd['data']['jitter'],
        $timesyncd['data']['delay']] = explode("\n", (string) $legacy);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_def = RrdDefinition::make()
    ->addDataset('offset', 'GAUGE', -1000, 1000)
    ->addDataset('frequency', 'GAUGE', -1000, 1000)
    ->addDataset('jitter', 'GAUGE', -1000, 1000)
    ->addDataset('delay', 'GAUGE', -1000, 1000);

$fields = [
    'offset' => $timesyncd['data']['offset'],
    'frequency' => $timesyncd['data']['frequency'],
    'jitter' => $timesyncd['data']['jitter'],
    'delay' => $timesyncd['data']['delay'],
];

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id],
    'rrd_def' => $rrd_def,
];
app('Datastore')->put($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
