<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'mysql', $app->app_id]);

$array = [
    'QCQICe' => [
        'descr' => 'Queries in cache',
        'colour' => '22FF22',
    ],
    'QCHs' => [
        'descr' => 'Cache hits',
        'colour' => '0022FF',
    ],
    'QCIs' => [
        'descr' => 'Inserts',
        'colour' => 'FF0000',
    ],
    'QCNCd' => [
        'descr' => 'Not cached',
        'colour' => '00AAAA',
    ],
    'QCLMPs' => [
        'descr' => 'Low-memory prunes',
        'colour' => 'FF00FF',
    ],
];

$rrd_list = [];
foreach ($array as $ds => $var) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => $var['descr'],
        'ds' => $ds,
        //                'colour' => $var['colour']
    ];
}


$colours = 'mixed';
$nototal = 1;
$unit_text = 'Commands';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
