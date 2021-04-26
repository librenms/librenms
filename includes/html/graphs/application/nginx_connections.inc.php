<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'nginx', $app['app_id']]);

$array = [
    'Reading' => [
        'descr'  => 'Reading',
        'colour' => '750F7DFF',
    ],
    'Writing' => [
        'descr'  => 'Writing',
        'colour' => '00FF00FF',
    ],
    'Waiting' => [
        'descr'  => 'Waiting',
        'colour' => '4444FFFF',
    ],
    'Active'  => [
        'descr'  => 'Starting',
        'colour' => '157419FF',
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
