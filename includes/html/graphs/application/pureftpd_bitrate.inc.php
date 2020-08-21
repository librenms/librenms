<?php
require 'includes/html/graphs/common.inc.php';
$scale_min     = 0;
$nototal       = 1;
$unit_text     = 'bit/s';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$colours       = 'mixed';

$array = array(
    'download' => 'Download',
    'upload'   => 'Upload',
);

$rrd_filename = rrd_name($device['hostname'], array('app', 'pureftpd', $app['app_id'], 'bitrate'));

$rrd_list = array();
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $i = 0;
    foreach ($array as $ds => $descr) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $descr;
        $rrd_list[$i]['ds']       = $ds;
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
