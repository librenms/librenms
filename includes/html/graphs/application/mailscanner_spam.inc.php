<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$nototal = (($width < 550) ? 1 : 0);
$unit_text = 'Messages/sec';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'mailscannerV2', $app['app_id']]);
$array = [
    'spam'  => [
        'descr'  => 'Spam',
        'colour' => 'FF8800',
    ],
    'virus' => [
        'descr'  => 'Virus',
        'colour' => 'FF0000',
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
}

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
