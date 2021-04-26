<?php

$name = 'postfix';
$app_id = $app['app_id'];
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Messages';
$unitlen = 11;
$bigdescrlen = 11;
$smalldescrlen = 11;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list = [
        [
            'filename' => $rrd_filename,
            'descr'    => 'Senders',
            'ds'       => 'senders',
            'colour'   => '582A72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Sending H/D',
            'ds'       => 'sendinghd',
            'colour'   => '28774F',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Recipients',
            'ds'       => 'recipients',
            'colour'   => '88CC88',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Recip H/D',
            'ds'       => 'recipienthd',
            'colour'   => 'D46A6A',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
