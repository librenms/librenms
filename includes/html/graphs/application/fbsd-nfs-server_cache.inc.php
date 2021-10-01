<?php

$name = 'fbsd-nfs-server';
$app_id = $app['app_id'];
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'per second';
$unitlen = 10;
$bigdescrlen = 10;
$smalldescrlen = 10;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list = [
        [
            'filename' => $rrd_filename,
            'descr'    => 'inprog',
            'ds'       => 'inprog',
            'colour'   => '582a72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'idem',
            'ds'       => 'idem',
            'colour'   => 'ffd1aa',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'nonidem',
            'ds'       => 'nonidem',
            'colour'   => 'aa6c39',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'misses',
            'ds'       => 'misses',
            'colour'   => '28536c',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
