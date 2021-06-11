<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'mixed';
$nototal = (($width < 224) ? 1 : 0);
$unit_text = 'Packets/sec';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'powerdns', $app['app_id']]);
$array = [
    'q_tcpAnswers' => [
        'descr'  => 'TCP Answers',
        'colour' => '008800FF',
    ],
    'q_tcpQueries' => [
        'descr'  => 'TCP Queries',
        'colour' => '00FF00FF',
    ],
    'q_udpAnswers' => [
        'descr'  => 'UDP Answers',
        'colour' => '336699FF',
    ],
    'q_udpQueries' => [
        'descr'  => 'UDP Queries',
        'colour' => '6699CCFF',
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
