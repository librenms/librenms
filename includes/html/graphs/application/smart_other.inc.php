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
        'descr'    => 'Spin_Retry_Count',
        'ds'       => 'id10',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Runtime_Bad_Block',
        'ds'       => 'id183',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'End-to-End_Error',
        'ds'       => 'id184',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Reallocated_Event_Count',
        'ds'       => 'id197',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'UDMA_CRC_Error_Count',
        'ds'       => 'id199',
    ];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
