<?php
$name = 'postfix';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Bytes';
$unitlen       = 5;
$bigdescrlen   = 7;
$smalldescrlen = 7;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Received',
            'ds'       => 'bytesr',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Delivered',
            'ds'       => 'bytesd',
            'colour'   => '88CC88'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
