<?php

$name = 'smart';
$unit_text = '';
$unitlen = 10;
$bigdescrlen = 25;
$smalldescrlen = 25;
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['disk']]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Temperature_Celsius',
        'ds'       => 'id194',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Airflow_Temperature_Cel',
        'ds'       => 'id190',
    ];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
