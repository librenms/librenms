<?php
$name = 'postfix';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Deferals';
$unitlen       = 13;
$bigdescrlen   = 13;
$smalldescrlen = 13;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Conn. Refused',
            'ds'       => 'deferralcr',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Host Is Down',
            'ds'       => 'deferralhid',
            'colour'   => '88CC88'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
