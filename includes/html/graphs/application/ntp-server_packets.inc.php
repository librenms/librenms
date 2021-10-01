<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$nototal = (($width < 224) ? 1 : 0);
$unit_text = 'Packets';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'ntp-server', $app['app_id']]);
$array = [
    'packets_drop'   => [
        'descr'  => 'Dropped',
        'colour' => '880000FF',
    ],
    'packets_ignore' => [
        'descr'  => 'Ignored',
        'colour' => 'FF8800FF',
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
