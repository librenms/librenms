<?php

$name = 'detailedcpu';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = '';
$unitlen       = 10;
$bigdescrlen   = 9;
$smalldescrlen = 9;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'User',
            'ds'       => 'userstats',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'System',
            'ds'       => 'systemstats',
            'colour'   => '28774F'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Idle',
            'ds'       => 'idlestats',
            'colour'   => '88CC88'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'IO Wait',
            'ds'       => 'iowaitstats',
            'colour'   => 'D46A6A'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Steal',
            'ds'       => 'stealstats',
            'colour'   => '800000'
        )
    );
} 

else {

    echo "file missing: $rrd_filename";
    
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
