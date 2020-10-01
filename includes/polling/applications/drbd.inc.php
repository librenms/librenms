<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'drbd';
$app_instance = $app['app_instance'];
$app_id = $app['app_id'];
$drbd_data = $agent_data['app'][$name][$app_instance];
foreach (explode('|', $drbd_data) as $part) {
    [$stat, $val] = explode('=', $part);
    if (! empty($stat)) {
        $drbd[$stat] = $val;
    }
}

$rrd_name = ['app', $name, $app_instance];
$rrd_def = RrdDefinition::make()
    ->addDataset('ns', 'DERIVE', 0, 125000000000)
    ->addDataset('nr', 'DERIVE', 0, 125000000000)
    ->addDataset('dw', 'DERIVE', 0, 125000000000)
    ->addDataset('dr', 'DERIVE', 0, 125000000000)
    ->addDataset('al', 'DERIVE', 0, 125000000000)
    ->addDataset('bm', 'DERIVE', 0, 125000000000)
    ->addDataset('lo', 'GAUGE', 0, 125000000000)
    ->addDataset('pe', 'GAUGE', 0, 125000000000)
    ->addDataset('ua', 'GAUGE', 0, 125000000000)
    ->addDataset('ap', 'GAUGE', 0, 125000000000)
    ->addDataset('oos', 'GAUGE', 0, 125000000000);

$fields = [
    'ns'  => $drbd['ns'],
    'nr'  => $drbd['nr'],
    'dw'  => $drbd['dw'],
    'dr'  => $drbd['dr'],
    'al'  => $drbd['al'],
    'bm'  => $drbd['bm'],
    'lo'  => $drbd['lo'],
    'pe'  => $drbd['pe'],
    'ua'  => $drbd['ua'],
    'ap'  => $drbd['ap'],
    'oos' => $drbd['oos'],
];

$tags = ['name', 'app_id', 'rrd_name', 'rrd_def'];
data_update($device, 'app', $tags, $fields);
update_application($app, $drbd_data, $fields);

unset($drbd, $drbd_data);
