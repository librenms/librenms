<?php

require 'includes/graphs/common.inc.php';

$scale_min    = 0;
$nototal      = (($width < 224) ? 1 : 0);
$unit_text    = 'Packets';
$rrd_filename = rrd_name($device['hostname'], array('app', 'ntpdserver', $app['app_id']));
$array        = array(
    'packets_drop'   => array(
        'descr'  => 'Dropped',
        'colour' => '880000FF',
    ),
    'packets_ignore' => array(
        'descr'  => 'Ignored',
        'colour' => 'FF8800FF',
    ),
);

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $vars) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $vars['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $vars['colour'];
        $i++;
    }
}
else {
    echo "file missing: $file";
}

// include("includes/graphs/generic_multi_line.inc.php");
require 'includes/graphs/generic_multi_simplex_seperated.inc.php';
