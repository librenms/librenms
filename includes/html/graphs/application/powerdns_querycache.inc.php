<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'mixed';
$nototal = 0;
$unit_text = 'Packets/sec';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'powerdns', $app['app_id']]);
$array = [
    'qc_miss' => [
        'descr'  => 'Misses',
        'colour' => '750F7DFF',
    ],
    'qc_hit'  => [
        'descr'  => 'Hits',
        'colour' => '00FF00FF',
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

require 'includes/html/graphs/generic_multi_line.inc.php';
