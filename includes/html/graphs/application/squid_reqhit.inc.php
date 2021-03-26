<?php

$name = 'squid';
$app_id = $app['app_id'];
$colours = 'mixed';
$unit_text = 'hit ratio';
$unitlen = 9;
$bigdescrlen = 9;
$smalldescrlen = 9;
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
            'ds'       => 'reqhitratio1',
            'colour'   => '582a72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => '5 minute',
            'ds'       => 'reqhitratio5',
            'colour'   => '28774f',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => '60 minute',
            'ds'       => 'reqhitratio60',
            'colour'   => '28536c',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
