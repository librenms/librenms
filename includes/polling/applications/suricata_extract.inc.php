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

$rrd_def = RrdDefinition::make()
    ->addDataset('errors', 'GAUGE', 0)
    ->addDataset('ignored_host', 'GAUGE', 0)
    ->addDataset('ignored_ip', 'GAUGE', 0)
    ->addDataset('ignored_ip_dest', 'GAUGE', 0)
    ->addDataset('ignored_ip_src', 'GAUGE', 0)
    ->addDataset('sub', 'GAUGE', 0)
    ->addDataset('sub_2xx', 'GAUGE', 0)
    ->addDataset('sub_3xx', 'GAUGE', 0)
    ->addDataset('sub_4xx', 'GAUGE', 0)
    ->addDataset('sub_5xx', 'GAUGE', 0)
    ->addDataset('sub_fail', 'GAUGE', 0)
    ->addDataset('truncated', 'GAUGE', 0)
    ->addDataset('zero_sized', 'GAUGE', 0)
    ->addDataset('sub_size', 'GAUGE', 0);

$fields = [
    'errors' => $data['errors_delta'],
    'ignored_host' => $data['ignored_host_delta'],
    'ignored_ip' => $data['ignored_ip_delta'],
    'ignored_ip_dest' => $data['ignored_ip_dest_delta'],
    'ignored_ip_src' => $data['ignored_ip_src_delta'],
    'sub' => $data['sub_delta'],
    'sub_2xx' => $data['sub_2xx_delta'],
    'sub_3xx' => $data['sub_3xx_delta'],
    'sub_4xx' => $data['sub_4xx_delta'],
    'sub_5xx' => $data['sub_5xx_delta'],
    'sub_fail' => $data['sub_fail_delta'],
    'truncated' => $data['truncated_delta'],
    'zero_sized' => $data['zero_sized_delta'],
    'sub_size' => $data['sub_size_delta'],
];

if (isset($data['last_errors']) && isset($data['last_errors'][0])) {
    log_event('suricata_extract_submit errors found: ' . json_encode($data['last_errors']), $device, 'application', 5);
}

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id],
    'rrd_def' => $rrd_def,
];
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
