<?php

$name = 'postfix';
$app_id = $app['app_id'];
$scale_min = 0;
$colours = 'mixed';
$unit_text = '';
$unitlen = 10;
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
            'descr'    => 'Incoming',
            'ds'       => 'incomingq',
            'colour'   => '582A72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Active',
            'ds'       => 'activeq',
            'colour'   => '28774F',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Deferred',
            'ds'       => 'deferredq',
            'colour'   => '88CC88',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Hold',
            'ds'       => 'holdq',
            'colour'   => 'D46A6A',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
