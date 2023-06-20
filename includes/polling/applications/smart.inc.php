<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'smart';

try {
    $data = json_app_get($device, $name)['data'];
} catch (JsonAppParsingFailedException $e) {
    // Legacy script, build compatible array
    $legacy = $e->getOutput();


    $lines = explode("\n", $output);

    $data=['disks'=>[]];

    $int = 0;
    while (isset($lines[$int])) {
        [$disk, $id5, $id10, $id173, $id177, $id183, $id184, $id187, $id188, $id190, $id194,
            $id196, $id197, $id198, $id199, $id231, $id233, $completed, $interrupted, $read_failure,
            $unknown_failure, $extended, $short, $conveyance, $selective] = explode(',', $lines[$int]);
        $int++;

        $data['disks'][$disk] = [
            '10' => $id10,
            '173' => $id173,
            '177' => $id177,
            '183' => $id183,
            '184' => $id184,
            '187' => $id187,
            '188' => $id188,
            '190' => $id190,
            '194' => $id194,
            '196' => $id196,
            '197' => $id197,
            '198' => $id198,
            '199' => $id199,
            '231' => $id231,
            '233' => $id233,
            '5' => $id5,
            '9' => $id9,
            'completed' => $completed,
            'interrupted' => $interrupted,
            'read_failure' => $read_failure,
            'unknown_failure' => $unknown_failure,
            'extended' => $extended,
            'short' => $short,
            'conveyance' => $conveyance,
            'selective' => 'selective',
        ];
    }
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app->app_id];
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

$rrd_def_id9 = RrdDefinition::make()
    ->addDataset('id9', 'GAUGE', 0);

$disks_with_failed_tests=[];

$int = 0;
$metrics = [];
foreach ($data['disks'] as $disk_id => $disk) {
    $rrd_name = ['app', $name, $app->app_id, $disk_id];

    $fields = [
        'id5' => is_numeric($disk['5']) ? $id5 : null,
        'id10' => is_numeric($disk['10']) ? $id10 : null,
        'id173' => is_numeric($disk['173']) ? $id173 : null,
        'id177' => is_numeric($disk['177']) ? $id177 : null,
        'id183' => is_numeric($disk['183']) ? $id183 : null,
        'id184' => is_numeric($disk['184']) ? $id184 : null,
        'id187' => is_numeric($disk['187']) ? $id187 : null,
        'id188' => is_numeric($disk['188']) ? $id188 : null,
        'id190' => is_numeric($disk['190']) ? $id190 : null,
        'id194' => is_numeric($disk['194']) ? $id194 : null,
        'id196' => is_numeric($disk['196']) ? $id196 : null,
        'id197' => is_numeric($disk['197']) ? $id197 : null,
        'id198' => is_numeric($disk['198']) ? $id198 : null,
        'id199' => is_numeric($disk['199']) ? $id199 : null,
        'id231' => is_numeric($disk['231']) ? $id231 : null,
        'id233' => is_numeric($disk['233']) ? $id233 : null,
        'completed' => is_numeric($disk['completed']) ? $disk['completed'] : null,
        'interrupted' => is_numeric($disk['interrupted']) ? $disk['interrupted'] : null,
        'readfailure' => is_numeric($disk['read_failure']) ? $disk['read_failure'] : null,
        'unknownfail' => is_numeric($disk['unknown_failure']) ? $disk['unknown_failure'] : null,
        'extended' => is_numeric($disk['extended']) ? $disk['extended'] : null,
        'short' => is_numeric($disk['short']) ? $disk['short'] : null,
        'conveyance' => is_numeric($disk['conveyance']) ? $disk['conveyance'] : null,
        'selective' => is_numeric($disk['selective']) ? $disk['selective'] : null,
    ];

    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $rrd_name_id9 = ['app', $name . '_id9', $app->app_id, $disk];
    $fields_id9 = ['id9' => $id9];
    $tags_id9 = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_id9, 'rrd_name' => $rrd_name_id9];
    data_update($device, 'app', $tags_id9, $fields_id9);

    if ((is_numeric($disk['read_failure']) && $disk['read_failure'] > 0) ||
      (is_numeric($disk['unknown_failure']) && $disk['unknown_failure'] > 0)) {
        array_push($disks_with_failed_tests, $disk_id);
    }

    $int++;
}

$app->data=$data;

update_application($app, $output, $data);
