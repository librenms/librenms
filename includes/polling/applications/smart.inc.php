<?php

use LibreNMS\RRD\RrdDefinition;

echo 'SMART';

$name = 'smart';
$app_id = $app['app_id'];

$options = '-Oqv';
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.5.115.109.97.114.116';
$output = snmp_walk($device, $oid, $options);

$lines = explode("\n", $output);

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('id5', 'GAUGE', 0)
    ->addDataset('id10', 'GAUGE', 0)
    ->addDataset('id173', 'GAUGE', 0)
    ->addDataset('id177', 'GAUGE', 0)
    ->addDataset('id183', 'GAUGE', 0)
    ->addDataset('id184', 'GAUGE', 0)
    ->addDataset('id187', 'GAUGE', 0)
    ->addDataset('id188', 'GAUGE', 0)
    ->addDataset('id190', 'GAUGE', 0)
    ->addDataset('id194', 'GAUGE', 0)
    ->addDataset('id196', 'GAUGE', 0)
    ->addDataset('id197', 'GAUGE', 0)
    ->addDataset('id198', 'GAUGE', 0)
    ->addDataset('id199', 'GAUGE', 0)
    ->addDataset('id231', 'GAUGE', 0)
    ->addDataset('id233', 'GAUGE', 0)
    ->addDataset('completed', 'GAUGE', 0)
    ->addDataset('interrupted', 'GAUGE', 0)
    ->addDataset('readfailure', 'GAUGE', 0)
    ->addDataset('unknownfail', 'GAUGE', 0)
    ->addDataset('extended', 'GAUGE', 0)
    ->addDataset('short', 'GAUGE', 0)
    ->addDataset('conveyance', 'GAUGE', 0)
    ->addDataset('selective', 'GAUGE', 0);

$int = 0;
$metrics = [];
while (isset($lines[$int])) {
    [$disk, $id5, $id10, $id173, $id177, $id183, $id184, $id187, $id188, $id190, $id194,
        $id196, $id197, $id198, $id199, $id231, $id233, $completed, $interrupted, $read_failure,
        $unknown_failure, $extended, $short, $conveyance, $selective] = explode(',', $lines[$int]);

    $rrd_name = ['app', $name, $app_id, $disk];

    $fields = [
        'id5' => is_numeric($id5) ? $id5 : null,
        'id10' => is_numeric($id10) ? $id10 : null,
        'id173' => is_numeric($id173) ? $id173 : null,
        'id177' => is_numeric($id177) ? $id177 : null,
        'id183' => is_numeric($id183) ? $id183 : null,
        'id184' => is_numeric($id184) ? $id184 : null,
        'id187' => is_numeric($id187) ? $id187 : null,
        'id188' => is_numeric($id188) ? $id188 : null,
        'id190' => is_numeric($id190) ? $id190 : null,
        'id194' => is_numeric($id194) ? $id194 : null,
        'id196' => is_numeric($id196) ? $id196 : null,
        'id197' => is_numeric($id197) ? $id197 : null,
        'id198' => is_numeric($id198) ? $id198 : null,
        'id199' => is_numeric($id199) ? $id199 : null,
        'id231' => is_numeric($id231) ? $id231 : null,
        'id233' => is_numeric($id233) ? $id233 : null,
        'completed' => is_numeric($completed) ? $completed : null,
        'interrupted' => is_numeric($interrupted) ? $interrupted : null,
        'readfailure' => is_numeric($read_failure) ? $read_failure : null,
        'unknownfail' => is_numeric($unknown_failure) ? $unknown_failure : null,
        'extended' => is_numeric($extended) ? $extended : null,
        'short' => is_numeric($short) ? $short : null,
        'conveyance' => is_numeric($conveyance) ? $conveyance : null,
        'selective' => is_numeric($selective) ? $selective : null,
    ];

    $metrics[$disk] = $fields;
    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $int++;
}

// smart enhancement id9
$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('id9', 'GAUGE', 0);

$int = 0;
while (isset($lines[$int])) {
    [$disk, , , , , , , , , , , , , , , , , , , , , , , , , $id9] = explode(',', $lines[$int]);

    $rrd_name = ['app', $name . '_id9', $app_id, $disk];

    $fields = ['id9' => $id9];
    $metrics[$disk]['id9'] = $id9;

    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $int++;
}

update_application($app, $output, $metrics);
