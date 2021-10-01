<?php

$name = 'fbsd-nfs-client';
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
            'descr'    => 'timed out',
            'ds'       => 'timedout',
            'colour'   => '582a72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'invalid',
            'ds'       => 'invalid',
            'colour'   => 'ffd1aa',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'x replies',
            'ds'       => 'xreplies',
            'colour'   => 'aa6c39',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'retries',
            'ds'       => 'retries',
            'colour'   => '28536c',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'requests',
            'ds'       => 'requests',
            'colour'   => 'ff11bb',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
