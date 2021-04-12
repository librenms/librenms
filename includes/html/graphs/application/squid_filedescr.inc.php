<?php

$name = 'squid';
$app_id = $app['app_id'];
$colours = 'mixed';
$unit_text = 'file descr.';
$unitlen = 11;
$bigdescrlen = 11;
$smalldescrlen = 11;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list = [
        [
            'filename' => $rrd_filename,
            'descr'    => 'in use',
            'ds'       => 'curfiledescrcnt',
            'colour'   => '28536c',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'max',
            'ds'       => 'curfiledescrmax',
            'colour'   => 'd46a6a',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
