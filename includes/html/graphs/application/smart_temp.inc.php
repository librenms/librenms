<?php
$name = 'smart';
$app_id = $app['app_id'];
$unit_text     = '';
$unitlen       = 10;
$bigdescrlen   = 25;
$smalldescrlen = 25;
$colours       = 'mega';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id, $vars['disk']));

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Temperature_Celsius',
        'ds'       => 'id194',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Airflow_Temperature_Cel',
        'ds'       => 'id190',
    );
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
