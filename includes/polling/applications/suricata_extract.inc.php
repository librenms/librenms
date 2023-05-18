<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'suricata_extract';

try {
    $data = json_app_get($device, $name)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('errors', 'DERIVE', 0)
    ->addDataset('ignored_host', 'DERIVE', 0)
    ->addDataset('ignored_ip', 'DERIVE', 0)
    ->addDataset('ignored_ip_dest', 'DERIVE', 0)
    ->addDataset('ignored_ip_src', 'DERIVE', 0)
    ->addDataset('sub', 'DERIVE', 0)
    ->addDataset('sub_2xx', 'DERIVE', 0)
    ->addDataset('sub_3xx', 'DERIVE', 0)
    ->addDataset('sub_4xx', 'DERIVE', 0)
    ->addDataset('sub_5xx', 'DERIVE', 0)
    ->addDataset('sub_fail', 'DERIVE', 0)
    ->addDataset('truncated', 'DERIVE', 0)
    ->addDataset('zero_sized', 'DERIVE', 0);

$fields = [
    'errors' => $data['errors'],
    'ignored_host' => $data['ignored_host'],
    'ignored_ip' => $data['ignored_ip'],
    'ignored_ip_dest' => $data['ignored_ip_dest'],
    'ignored_ip_src' => $data['ignored_ip_src'],
    'sub' => $data['sub'],
    'sub_2xx' => $data['sub_2xx'],
    'sub_3xx' => $data['sub_3xx'],
    'sub_4xx' => $data['sub_4xx'],
    'sub_5xx' => $data['sub_5xx'],
    'sub_fail' => $data['sub_fail'],
    'truncated' => $data['truncated'],
    'zero_sized' => $data['zero_sized'],
];

if (isset($data['last_errors']) && isset($data['last_errors'][0])) {
    log_event('suricata_extract_submit errors found: ' . json_encode($data['last_errors']), $device, 'application', 5);
}

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
