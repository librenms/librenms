<?php

$name = 'postfix';
$app_id = $app['app_id'];
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Messages';
$unitlen = 8;
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
            'descr'    => 'Received',
            'ds'       => 'received',
            'colour'   => '582A72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Delivered',
            'ds'       => 'delivered',
            'colour'   => '28774F',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Deferred',
            'ds'       => 'deferred',
            'colour'   => 'AA6C39',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Bounced',
            'ds'       => 'bounced',
            'colour'   => '88CC88',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Rejected',
            'ds'       => 'rejected',
            'colour'   => 'D46A6A',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Reject Warnings',
            'ds'       => 'rejectw',
            'colour'   => 'FFD1AA',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Held',
            'ds'       => 'held',
            'colour'   => '582A72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Discarded',
            'ds'       => 'discarded',
            'colour'   => 'AA5439',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
