<?php

use LibreNMS\RRD\RrdDefinition;

echo('SMART');

$name = 'smart';
$app_id = $app['app_id'];

$options      = '-Oqv';
$oid          = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.5.115.109.97.114.116';
$output = snmp_walk($device, $oid, $options);

$lines = explode("\n", $output);

$rrd_name = array('app', $name, $app_id);
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

$int=0;
$metrics = array();
while (isset($lines[$int])) {
    list($disk, $id5, $id10, $id173, $id177, $id183, $id184, $id187, $id188, $id190, $id194,
        $id196, $id197, $id198, $id199, $id231, $id233, $completed, $interrupted, $read_failure,
        $unknown_failure, $extended, $short, $conveyance, $selective)=explode(",", $lines[$int]);

    if (is_int($id5)) {
        $id=null;
    }
    if (is_int($id10)) {
        $id10=null;
    }
    if (is_int($id173)) {
        $id173=null;
    }
    if (is_int($id177)) {
        $id177=null;
    }
    if (is_int($id183)) {
        $id183=null;
    }
    if (is_int($id184)) {
        $id184=null;
    }
    if (is_int($id187)) {
        $id187=null;
    }
    if (is_int($id188)) {
        $id188=null;
    }
    if (is_int($id190)) {
        $id190=null;
    }
    if (is_int($id194)) {
        $id194=null;
    }
    if (is_int($id196)) {
        $id196=null;
    }
    if (is_int($id197)) {
        $id197=null;
    }
    if (is_int($id198)) {
        $id198=null;
    }
    if (is_int($id199)) {
        $id199=null;
    }
    if (is_int($id231)) {
        $id231=null;
    }
    if (is_int($id233)) {
        $id233=null;
    }

    $rrd_name = array('app', $name, $app_id, $disk);

    $fields = array(
        'id5'=>$id5,
        'id10'=>$id10,
        'id173'=>$id173,
        'id177'=>$id177,
        'id183'=>$id183,
        'id184'=>$id184,
        'id187'=>$id187,
        'id188'=>$id188,
        'id190'=>$id190,
        'id194'=>$id194,
        'id196'=>$id196,
        'id197'=>$id197,
        'id198'=>$id198,
        'id199'=>$id199,
        'id231'=>$id231,
        'id233'=>$id233,
        'completed'=>$completed,
        'interrupted'=>$interrupted,
        'readfailure'=>$read_failure,
        'unknownfail'=>$unknown_failure,
        'extended'=>$extended,
        'short'=>$short,
        'conveyance'=>$conveyance,
        'selective'=>$selective
    );

    $metrics[$disk] = $fields;
    $tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
    data_update($device, 'app', $tags, $fields);

    $int++;
}


# smart enhancement id9
$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('id9', 'GAUGE', 0);

$int=0;
while (isset($lines[$int])) {
    list($disk, , , , , , , , , , , , , , , , , , , , , , , , , $id9)=explode(",", $lines[$int]);

    $rrd_name = array('app', $name.'_id9', $app_id, $disk);

    $fields = ['id9' => $id9];
    $metrics[$disk]['id9'] = $id9;
    
    $tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
    data_update($device, 'app', $tags, $fields);

    $int++;
}

update_application($app, $output, $metrics);
