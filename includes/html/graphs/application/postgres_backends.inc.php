<?php
$name = 'postgres';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = '';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

if (isset($vars['database'])) {
    $rrd_name_array=array('app', $name, $app_id, $vars['database']);
} else {
    $rrd_name_array=array('app', $name, $app_id);
}

$rrd_filename = rrd_name($device['hostname'], $rrd_name_array);

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Backends',
            'ds'       => 'backends',
            'colour'   => '582A72'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
