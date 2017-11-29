<?php
require 'includes/graphs/common.inc.php';
$i            = 0;
$scale_min    = 0;
$nototal      = 1;
$unit_text    = 'Per Sec.';
$rrd_filename = rrd_name($device['hostname'], array('app', 'freeradius-queue', $app['app_id']));
$array        = array(
    'len_internal',
    'len_proxy',
    'len_auth',
    'len_acct',
    'len_detail',
    'pps_in',
    'pps_out'
);
$colours      = 'mixed';
$rrd_list     = array();
if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = strtoupper($ds);
        $rrd_list[$i]['ds']       = $ds;
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}
require 'includes/graphs/generic_multi_line.inc.php';
