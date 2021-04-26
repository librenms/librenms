<?php

$name = 'squid';
$app_id = $app['app_id'];
$colours = 'mixed';
$unit_text = 'byte hit ratio';
$unitlen = 15;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list = [
        [
            'filename' => $rrd_filename,
            'descr'    => '1 minute',
            'ds'       => 'reqbyteratio1',
            'colour'   => '582a72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => '5 minute',
            'ds'       => 'reqbyteratio5',
            'colour'   => '28774f',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => '60 minute',
            'ds'       => 'reqbyteratio60',
            'colour'   => '28536c',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
