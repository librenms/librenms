<?php
$name = 'smart';
$app_id = $app['app_id'];
$unit_text     = '';
$unitlen       = 20;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$colours       = 'mega';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id, $vars['disk']));

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Completed',
        'ds'       => 'completed',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Interrupted',
        'ds'       => 'interrupted',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Read Failure',
        'ds'       => 'readfailure',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'Unknown Failure',
        'ds'       => 'unknownfail',
    );
}



require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
