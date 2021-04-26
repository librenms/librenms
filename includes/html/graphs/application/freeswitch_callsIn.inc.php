<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'blue';
$nototal = (($width < 224) ? 1 : 0);
$unit_text = 'Inbound Calls/sec';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'freeswitch', 'stats', $app['app_id']]);
$array = [
    'in_okay'  => [
        'descr'  => 'Okay',
        'colour' => '008800FF',
    ],
    'in_failed' => [
        'descr'  => 'Failed',
        'colour' => '880000FF',
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
