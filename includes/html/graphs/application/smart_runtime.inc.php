<?php

$name = 'smart_id9';
$app_id = $app['app_id'];
$unit_text = '';
$unitlen = 20;
$bigdescrlen = 15;
$smalldescrlen = 15;
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $vars['disk']]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Power On Hours',
        'ds'       => 'id9',
    ];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
