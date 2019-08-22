<?php
require 'includes/html/graphs/common.inc.php';
$rrd_filename  = rrd_name($device['hostname'], 'rutos_2xx_mobileDataUsage');
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Usage';
$unitlen       = 21;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 80;
$data_sources  = array(
    'usage_sent' => array('descr' => 'Sent','colour' => '008C00',),
    'usage_received' => array('descr' => 'Received','colour' => '4096EE',),
);
$i = 0;
if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($data_sources as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}
require 'includes/html/graphs/generic_v3_multiline_float.inc.php';
