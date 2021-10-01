<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'blue';
$nototal = (($width < 224) ? 1 : 0);
$unit_text = 'Packets/sec';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'powerdns', $app['app_id']]);
$array = [
    'pc_hit'  => [
        'descr'  => 'Hits',
        'colour' => '008800FF',
    ],
    'pc_miss' => [
        'descr'  => 'Misses',
        'colour' => '880000FF',
    ],
    'pc_size' => [
        'descr'  => 'Size',
        'colour' => '006699FF',
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

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
