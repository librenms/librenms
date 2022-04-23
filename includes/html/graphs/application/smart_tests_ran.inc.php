<?php

$name = 'smart';
$app_id = $app['app_id'];
$unit_text = '';
$unitlen = 20;
$bigdescrlen = 10;
$smalldescrlen = 10;
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $vars['disk']]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Extended',
        'ds'       => 'extended',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Short',
        'ds'       => 'short',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Selective',
        'ds'       => 'selective',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Conveyance',
        'ds'       => 'conveyance',
    ];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
