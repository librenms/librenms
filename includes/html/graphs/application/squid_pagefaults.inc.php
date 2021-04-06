<?php

$name = 'squid';
$app_id = $app['app_id'];
$colours = 'mixed';
$unit_text = 'per second';
$unitlen = 10;
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
            'descr'    => 'page faults',
            'ds'       => 'syspagefaults',
            'colour'   => '582a72',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
