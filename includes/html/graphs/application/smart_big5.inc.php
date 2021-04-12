<?php

$name = 'smart';
$app_id = $app['app_id'];
$unit_text = '';
$unitlen = 10;
$bigdescrlen = 25;
$smalldescrlen = 25;
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $vars['disk']]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Reallocated_Sector_Ct',
        'ds'       => 'id5',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Reported_Uncorrect',
        'ds'       => 'id187',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Command_Timeout',
        'ds'       => 'id188',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Current_Pending_Sector',
        'ds'       => 'id197',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Offline_Uncorrectable',
        'ds'       => 'id198',
    ];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
