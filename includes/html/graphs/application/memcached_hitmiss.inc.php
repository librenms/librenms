<?php

require 'memcached.inc.php';
require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'mixed';
$nototal = 0;
$unit_text = 'Hits/Misses';
$array = [
    'get_hits'   => [
        'descr'  => 'Hits',
        'colour' => '555555',
    ],
    'get_misses' => [
        'descr'  => 'Misses',
        'colour' => 'cc0000',
    ],
];

$i = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = $var['colour'];
        if (! empty($var['areacolour'])) {
            $rrd_list[$i]['areacolour'] = $var['areacolour'];
        }

        $i++;
    }
} else {
    echo "file missing: $file";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
