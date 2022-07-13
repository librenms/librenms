<?php

require 'includes/html/graphs/common.inc.php';
$descr_len = 20;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'opensips', $app['app_id']]);

$array = [
    'total_memory' => [
        'descr'  => 'Total',
        'colour' => '22FF22',
    ],
    'used_memory' => [
        'descr'  => 'Used',
        'colour' => '0022FF',
    ],
];

$i = 0;
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        // $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $file";
}

$colours = 'mixed';
$nototal = 1;
$unit_text = 'bytes';

require 'includes/html/graphs/generic_multi_line.inc.php';
