<?php
$name = 'smart';
$app_id = $app['app_id'];
$unit_text     = '';
$unitlen       = 10;
$bigdescrlen   = 10;
$smalldescrlen = 10;
$colours       = 'mega';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id, $vars['disk']));

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'ID# 173',
        'ds'       => 'id173',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'ID# 231',
        'ds'       => 'id231',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'ID# 233',
        'ds'       => 'id233',
    );
}



require 'includes/graphs/generic_multi_line_exact_numbers.inc.php';
