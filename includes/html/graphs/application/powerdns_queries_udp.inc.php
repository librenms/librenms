<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'mixed';
$nototal = (($width < 224) ? 1 : 0);
$unit_text = 'Packets/sec';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'powerdns', $app['app_id']]);
$array = [
    'q_udp4Answers' => [
        'descr'  => 'UDP4 Answers',
        'colour' => '00008888',
    ],
    'q_udp4Queries' => [
        'descr'  => 'UDP4 Queries',
        'colour' => '000088FF',
    ],
    'q_udp6Answers' => [
        'descr'  => 'UDP6 Answers',
        'colour' => '88000088',
    ],
    'q_udp6Queries' => [
        'descr'  => 'UDP6 Queries',
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
