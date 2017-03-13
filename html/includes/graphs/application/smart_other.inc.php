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
        'descr'    => 'ID# 10',
        'ds'       => 'id10',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'ID# 183',
        'ds'       => 'id183',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'ID# 184',
        'ds'       => 'id184',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'ID# 196',
        'ds'       => 'id197',
    );
    $rrd_list[]=array(
        'filename' => $rrd_filename,
        'descr'    => 'ID# 199',
        'ds'       => 'id199',
    );
}

require 'includes/graphs/generic_multi_line_exact_numbers.inc.php';
