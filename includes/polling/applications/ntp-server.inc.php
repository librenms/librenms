<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'ntp-server';
$app_id = $app['app_id'];

echo $name;

try{
    $ntp=json_app_get($device, 'ntp-server', 1);
} catch (JsonAppPollingFailedException $e ){
    echo $e->getMessage();
    return;
}

$rrd_name = array('app', $name, $app_id);
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


$fields = array(
    'stratum'        => $ntp['stratum'],
    'offset'         => $ntp['offset'],
    'frequency'      => $ntp['frequency'],
    'jitter'         => $ntp['sys_jitter'],
    'noise'          => $ntp['clk_jitter'],
    'stability'      => $ntp['clk_wander'],
    'uptime'         => $ntp['time_since_reset'],
    'buffer_recv'    => $ntp['receive_buffers'],
    'buffer_free'    => $ntp['free_receive_buffers'],
    'buffer_used'    => $ntp['used_receive_buffers'],
    'packets_drop'   => $ntp['dropped_packets'],
    'packets_ignore' => $ntp['ignored_packets'],
    'packets_recv'   => $ntp['received_packets'],
    'packets_sent'   => $ntp['packets_sent'],
);


$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $ntp, $fields);
