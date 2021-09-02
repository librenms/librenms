<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'apache', $app['app_id']]);

$array = [
    'sb_reading'   => [
        'descr'  => 'Reading',
        'colour' => '750F7DFF',
    ],
    'sb_writing'   => [
        'descr'  => 'Writing',
        'colour' => '00FF00FF',
    ],
    'sb_wait'      => [
        'descr'  => 'Waiting',
        'colour' => '4444FFFF',
    ],
    'sb_start'     => [
        'descr'  => 'Starting',
        'colour' => '157419FF',
    ],
    'sb_keepalive' => [
        'descr'  => 'Keepalive',
        'colour' => 'FF0000FF',
    ],
    'sb_dns'       => [
        'descr'  => 'DNS',
        'colour' => '6DC8FEFF',
    ],
    'sb_closing'   => [
        'descr'  => 'Closing',
        'colour' => 'FFAB00FF',
    ],
    'sb_logging'   => [
        'descr'  => 'Logging',
        'colour' => 'FFFF00FF',
    ],
    'sb_graceful'  => [
        'descr'  => 'Graceful',
        'colour' => 'FF5576FF',
    ],
    'sb_idle'      => [
        'descr'  => 'Idle',
        'colour' => 'FF4105FF',
    ],
];

$i = 0;
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $file";
}

$colours = 'mixed';
$nototal = 1;
$unit_text = 'Workers';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
