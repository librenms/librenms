<?php

$name = 'squid';
$app_id = $app['app_id'];
$colours = 'mixed';
$unit_text = 'kilobytes';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list = [
        [
            'filename' => $rrd_filename,
            'descr'    => 'max mem',
            'ds'       => 'memmaxsize',
            'colour'   => '582a72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'max swap',
            'ds'       => 'swapmaxsize',
            'colour'   => '28774f',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'high swap WM',
            'ds'       => 'swaphighwm',
            'colour'   => '28536c',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'low swap WM',
            'ds'       => 'swaplowwm',
            'colour'   => 'd46a6a',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'swap usage',
            'ds'       => 'currentswapsize',
            'colour'   => 'ff11bb',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'memory usage',
            'ds'       => 'memusage',
            'colour'   => 'd853dc',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
