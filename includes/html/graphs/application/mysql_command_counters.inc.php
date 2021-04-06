<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'mysql', $app['app_id']]);

$array = [
    'CDe'  => [
        'descr'  => 'Delete',
        'colour' => '22FF22',
    ],
    'CIt'  => [
        'descr'  => 'Insert',
        'colour' => '0022FF',
    ],
    'CISt' => [
        'descr'  => 'Insert Select',
        'colour' => 'FF0000',
    ],
    'CLd'  => [
        'descr'  => 'Load Data',
        'colour' => '00AAAA',
    ],
    'CRe'  => [
        'descr'  => 'Replace',
        'colour' => 'FF00FF',
    ],
    'CRSt' => [
        'descr'  => 'Replace Select',
        'colour' => 'FFA500',
    ],
    'CSt'  => [
        'descr'  => 'Select',
        'colour' => 'CC0000',
    ],
    'CUe'  => [
        'descr'  => 'Update',
        'colour' => '0000CC',
    ],
    'CUMi' => [
        'descr'  => 'Update Multiple',
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
$unit_text = 'Commands';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
