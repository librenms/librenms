<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'ntp-server';
$app_id = $app['app_id'];

echo $name;

try {
    $ntp = json_app_get($device, $name);
} catch (JsonAppParsingFailedException $e) {
    // Legacy script, build compatible array
    $legacy = $e->getOutput();

    $ntp = [
        data => [],
    ];

    [$ntp['data']['stratum'], $ntp['data']['offset'], $ntp['data']['frequency'], $ntp['data']['jitter'],
          $ntp['data']['noise'], $ntp['data']['stability'], $ntp['data']['uptime'], $ntp['data']['buffer_recv'],
          $ntp['data']['buffer_free'], $ntp['data']['buffer_used'], $ntp['data']['packets_drop'],
          $ntp['data']['packets_ignore'], $ntp['data']['packets_recv'], $ntp['data']['packets_sent']] = explode("\n", $legacy);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('stratum', 'GAUGE', 0, 1000)
    ->addDataset('offset', 'GAUGE', -1000, 1000)
    ->addDataset('frequency', 'GAUGE', -1000, 1000)
    ->addDataset('jitter', 'GAUGE', -1000, 1000)
    ->addDataset('noise', 'GAUGE', -1000, 1000)
    ->addDataset('stability', 'GAUGE', -1000, 1000)
    ->addDataset('uptime', 'GAUGE', 0, 125000000000)
    ->addDataset('buffer_recv', 'GAUGE', 0, 100000)
    ->addDataset('buffer_free', 'GAUGE', 0, 100000)
    ->addDataset('buffer_used', 'GAUGE', 0, 100000)
    ->addDataset('packets_drop', 'DERIVE', 0, 125000000000)
    ->addDataset('packets_ignore', 'DERIVE', 0, 125000000000)
    ->addDataset('packets_recv', 'DERIVE', 0, 125000000000)
    ->addDataset('packets_sent', 'DERIVE', 0, 125000000000);

$fields = [
    'stratum'        => $ntp['data']['stratum'],
    'offset'         => $ntp['data']['offset'],
    'frequency'      => $ntp['data']['frequency'],
    'jitter'         => $ntp['data']['sys_jitter'],
    'noise'          => $ntp['data']['clk_jitter'],
    'stability'      => $ntp['data']['clk_wander'],
    'uptime'         => $ntp['data']['time_since_reset'],
    'buffer_recv'    => $ntp['data']['receive_buffers'],
    'buffer_free'    => $ntp['data']['free_receive_buffers'],
    'buffer_used'    => $ntp['data']['used_receive_buffers'],
    'packets_drop'   => $ntp['data']['dropped_packets'],
    'packets_ignore' => $ntp['data']['ignored_packets'],
    'packets_recv'   => $ntp['data']['received_packets'],
    'packets_sent'   => $ntp['data']['packets_sent'],
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
