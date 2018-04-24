<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'ntp-client';
$app_id = $app['app_id'];

echo $name;

try{
    $ntp=json_app_get($device, 'ntp-client', 1);
} catch (JsonAppPollingFailedException $e ){
    echo $e->getMessage();
    return;
}

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('offset', 'GAUGE', -1000, 1000)
    ->addDataset('frequency', 'GAUGE', -1000, 1000)
    ->addDataset('jitter', 'GAUGE', -1000, 1000)
    ->addDataset('noise', 'GAUGE', -1000, 1000)
    ->addDataset('stability', 'GAUGE', -1000, 1000);

$fields = array(
    'offset' => $ntp['offset'],
    'frequency' => $ntp['frequency'],
    'jitter' => $ntp['sys_jitter'],
    'noise' => $ntp['clk_jitter'],
    'stability' => $ntp['clk_wander'],
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $ntp, $fields);
