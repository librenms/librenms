<?php

require 'includes/html/graphs/common.inc.php';
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Operations';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$rrd_filename = Rrd::dirFromHost($device['hostname']) . '/app-nfsstats-' . $app['app_id'] . '.rrd';
$array = [
    'total' => [
        'descr'  => 'Total',
        'colour' => '000000',
    ],
    'null' => [
        'descr'  => 'NULL',
        'colour' => '630606',
    ],
    'getattr' => [
        'descr'  => 'Get attributes',
        'colour' => '50C150',
    ],
    'setattr' => [
        'descr'  => 'Set attributes',
        'colour' => '4D65A2',
    ],
    'lookup' => [
        'descr'  => 'Lookup',
        'colour' => '8B64A1',
    ],
    'access' => [
        'descr'  => 'Access',
        'colour' => 'AAAA39',
    ],
    'read' => [
        'descr'  => 'Read',
        'colour' => '',
    ],
    'write' => [
        'descr'  => 'Write',
        'colour' => '457A9A',
    ],
    'create' => [
        'descr'  => 'Create',
        'colour' => '690D87',
    ],
    'mkdir' => [
        'descr'  => 'Make dir',
        'colour' => '072A3F',
    ],
    'remove' => [
        'descr'  => 'Remove',
        'colour' => 'F16464',
    ],
    'rmdir' => [
        'descr'  => 'Remove dir',
        'colour' => '57162D',
    ],
    'rename' => [
        'descr'  => 'Rename',
        'colour' => 'A40B62',
    ],
    'readdirplus' => [
        'descr'  => 'Read dir plus',
        'colour' => 'F1F164',
    ],
    'fsstat' => [
        'descr'  => 'FS stat',
        'colour' => 'F1F191',
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

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
