<?php

require 'includes/html/graphs/common.inc.php';
$descr_len = 20;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'mysql', $app['app_id']]);

$array = [
    'MaCs' => [
        'descr'  => 'Max Connections',
        'colour' => '22FF22',
    ],
    'MUCs' => [
        'descr'  => 'Max Used Connections',
        'colour' => '0022FF',
    ],
    'ACs'  => [
        'descr'  => 'Aborted Clients',
        'colour' => 'FF0000',
    ],
    'AdCs' => [
        'descr'  => 'Aborted Connects',
        'colour' => '0080C0',
    ],
    'TCd'  => [
        'descr'  => 'Threads Connected',
        'colour' => 'FF0000',
    ],
    'Cs'   => [
        'descr'  => 'New Connections',
        'colour' => '0080C0',
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
$unit_text = 'Connections';

require 'includes/html/graphs/generic_multi_line.inc.php';
